<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WhatsAppService $whatsappService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->whatsappService = new WhatsAppService();
    }

    public function test_phone_number_formatting_with_zero_prefix()
    {
        $formatted = $this->whatsappService->formatPhoneNumber('081234567890');
        $this->assertEquals('6281234567890', $formatted);
    }

    public function test_phone_number_formatting_with_62_prefix()
    {
        $formatted = $this->whatsappService->formatPhoneNumber('6281234567890');
        $this->assertEquals('6281234567890', $formatted);
    }

    public function test_phone_number_formatting_with_plus_62_prefix()
    {
        $formatted = $this->whatsappService->formatPhoneNumber('+6281234567890');
        $this->assertEquals('6281234567890', $formatted);
    }

    public function test_phone_number_formatting_with_8_prefix()
    {
        $formatted = $this->whatsappService->formatPhoneNumber('81234567890');
        $this->assertEquals('6281234567890', $formatted);
    }

    public function test_phone_number_formatting_removes_spaces_and_dashes()
    {
        $formatted = $this->whatsappService->formatPhoneNumber('0812-3456-7890');
        $this->assertEquals('6281234567890', $formatted);

        $formatted = $this->whatsappService->formatPhoneNumber('0812 3456 7890');
        $this->assertEquals('6281234567890', $formatted);
    }

    public function test_valid_phone_number_validation()
    {
        $this->assertTrue($this->whatsappService->isValidPhoneNumber('6281234567890'));
        $this->assertTrue($this->whatsappService->isValidPhoneNumber('628123456789'));
        $this->assertTrue($this->whatsappService->isValidPhoneNumber('62812345678901'));
    }

    public function test_invalid_phone_number_validation()
    {
        $this->assertFalse($this->whatsappService->isValidPhoneNumber('081234567890'));
        $this->assertFalse($this->whatsappService->isValidPhoneNumber('621234567890')); // Doesn't start with 8 after 62
        $this->assertFalse($this->whatsappService->isValidPhoneNumber('62812345')); // Too short
        $this->assertFalse($this->whatsappService->isValidPhoneNumber(''));
    }

    public function test_send_message_success()
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => true,
                'reason' => 'Success',
            ], 200),
        ]);

        $result = $this->whatsappService->sendMessage('081234567890', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertEquals('Pesan berhasil dikirim', $result['message']);
    }

    public function test_send_message_failure()
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => false,
                'reason' => 'Invalid token',
            ], 401),
        ]);

        $result = $this->whatsappService->sendMessage('081234567890', 'Test message');

        $this->assertFalse($result['success']);
    }

    public function test_send_message_with_invalid_phone()
    {
        $result = $this->whatsappService->sendMessage('invalid', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertEquals('Nomor telepon tidak valid', $result['message']);
    }

    public function test_user_whatsapp_phone_attribute()
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
        ]);

        $this->assertEquals('6281234567890', $user->whatsapp_phone);
    }

    public function test_user_prefers_whatsapp()
    {
        $user = User::factory()->create([
            'notification_preference' => 'whatsapp',
        ]);

        $this->assertTrue($user->prefersWhatsApp());
        $this->assertFalse($user->prefersEmail());
    }

    public function test_user_prefers_email()
    {
        $user = User::factory()->create([
            'notification_preference' => 'email',
            'email' => 'test@example.com',
        ]);

        $this->assertFalse($user->prefersWhatsApp());
        $this->assertTrue($user->prefersEmail());
    }

    public function test_service_is_configured()
    {
        config(['whatsapp.fonnte.token' => 'test-token']);
        
        $service = new WhatsAppService();
        $this->assertTrue($service->isConfigured());
    }

    public function test_service_is_not_configured()
    {
        config(['whatsapp.fonnte.token' => null]);
        
        $service = new WhatsAppService();
        $this->assertFalse($service->isConfigured());
    }
}
