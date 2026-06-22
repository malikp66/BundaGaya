<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    private string $apiKey;
    private string $baseUrl;
    private array $origin;

    public function __construct()
    {
        $this->apiKey = config('shipping.biteship.api_key');
        $this->baseUrl = config('shipping.biteship.base_url', 'https://api.biteship.com/v1');
        $this->origin = config('shipping.warehouse');
    }

    public function getRates(array $destination, int $length, int $width, int $height, int $weight, array $couriers = []): array
    {
        $payload = [
            'origin_latitude' => $this->origin['latitude'],
            'origin_longitude' => $this->origin['longitude'],
            'destination_latitude' => $destination['latitude'],
            'destination_longitude' => $destination['longitude'],
            'origin_postal_code' => $this->origin['postal_code'],
            'destination_postal_code' => $destination['postal_code'],
            'couriers' => empty($couriers) ? $this->getDefaultCouriers() : $couriers,
            'items' => [
                [
                    'name' => 'Rental Item',
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'quantity' => 1,
                    'value' => 1,
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/rates/couriers", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return $this->formatRatesResponse($data['pricing'] ?? []);
            }

            Log::error('Biteship API error: ' . $response->body());

            return [];
        } catch (\Exception $e) {
            Log::error('Biteship API exception: ' . $e->getMessage());

            return [];
        }
    }

    public function getRatesByPostalCode(string $destinationPostalCode, int $length, int $width, int $height, int $weight, array $couriers = []): array
    {
        $payload = [
            'origin_postal_code' => $this->origin['postal_code'],
            'destination_postal_code' => $destinationPostalCode,
            'couriers' => empty($couriers) ? $this->getDefaultCouriers() : $couriers,
            'items' => [
                [
                    'name' => 'Rental Item',
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'quantity' => 1,
                    'value' => 1,
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/rates/postal-codes", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return $this->formatRatesResponse($data['pricing'] ?? []);
            }

            Log::error('Biteship API error: ' . $response->body());

            return [];
        } catch (\Exception $e) {
            Log::error('Biteship API exception: ' . $e->getMessage());

            return [];
        }
    }

    public function createShipment(string $destinationPostalCode, string $destinationAddress, string $destinationName, string $destinationPhone, int $length, int $width, int $height, int $weight, string $courier, string $service): ?array
    {
        $payload = [
            'origin_contact_name' => $this->origin['contact_name'],
            'origin_contact_phone' => $this->origin['contact_phone'],
            'origin_address' => $this->origin['address'],
            'origin_postal_code' => $this->origin['postal_code'],
            'origin_note' => $this->origin['note'] ?? '',
            'destination_contact_name' => $destinationName,
            'destination_contact_phone' => $destinationPhone,
            'destination_address' => $destinationAddress,
            'destination_postal_code' => $destinationPostalCode,
            'courier_company' => $courier,
            'courier_type' => $service,
            'items' => [
                [
                    'name' => 'Rental Item',
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => $weight,
                    'quantity' => 1,
                    'value' => 1,
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/shipments", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Biteship create shipment error: ' . $response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship create shipment exception: ' . $e->getMessage());

            return null;
        }
    }

    public function trackShipment(string $waybillId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->get("{$this->baseUrl}/trackings/{$waybillId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Biteship tracking error: ' . $response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Biteship tracking exception: ' . $e->getMessage());

            return null;
        }
    }

    private function getDefaultCouriers(): array
    {
        return ['jne', 'sicepat', 'jnt', 'anteraja'];
    }

    private function formatRatesResponse(array $pricing): array
    {
        $formatted = [];

        foreach ($pricing as $rate) {
            $courierCode = strtolower($rate['courier_code'] ?? '');
            $courierName = $rate['courier_name'] ?? $rate['courier_code'] ?? '';

            $formatted[] = [
                'courier_code' => $courierCode,
                'courier_name' => $courierName,
                'service' => $rate['courier_service_name'] ?? $rate['service_type'] ?? '',
                'description' => $rate['description'] ?? '',
                'price' => (int) ($rate['price'] ?? 0),
                'etd' => $rate['duration'] ?? $rate['etd'] ?? '',
                'rate_id' => $rate['rate_id'] ?? null,
                'type' => $rate['service_type'] ?? 'reguler',
            ];
        }

        return $formatted;
    }
}
