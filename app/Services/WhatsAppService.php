<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    private string $baseUrl;
    private ?string $token;
    private bool $logMessages;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.fonnte.base_url');
        $this->token = config('whatsapp.fonnte.token');
        $this->logMessages = config('whatsapp.settings.log_messages', true);
    }

    /**
     * Send WhatsApp message via Fonnte API
     *
     * @param string $phone Phone number (will be formatted automatically)
     * @param string $message Message content
     * @return array Response from API
     */
    public function sendMessage(string $phone, string $message): array
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        
        if (!$this->isValidPhoneNumber($formattedPhone)) {
            $this->log('error', 'Invalid phone number', [
                'original' => $phone,
                'formatted' => $formattedPhone,
            ]);
            
            return [
                'success' => false,
                'message' => 'Nomor telepon tidak valid',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'target' => $formattedPhone,
                'message' => $message,
            ]);

            $result = $response->json();
            $success = $response->successful() && ($result['status'] ?? false);

            $this->log($success ? 'info' : 'error', 'WhatsApp message sent', [
                'phone' => $formattedPhone,
                'message' => Str::limit($message, 100),
                'response' => $result,
                'status_code' => $response->status(),
            ]);

            return [
                'success' => $success,
                'message' => $success ? 'Pesan berhasil dikirim' : ($result['reason'] ?? 'Gagal mengirim pesan'),
                'data' => $result,
            ];

        } catch (\Exception $e) {
            $this->log('error', 'WhatsApp message failed', [
                'phone' => $formattedPhone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to international format (62xxx)
     *
     * @param string $phone Phone number in any format
     * @return string Formatted phone number
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Handle different formats
        if (Str::startsWith($phone, '0')) {
            // 0812... -> 62812...
            $phone = '62' . substr($phone, 1);
        } elseif (Str::startsWith($phone, '62')) {
            // Already in correct format: 62812...
            $phone = $phone;
        } elseif (Str::startsWith($phone, '8')) {
            // 812... -> 62812...
            $phone = '62' . $phone;
        } elseif (Str::startsWith($phone, '1')) {
            // 1812... (US format, unlikely but handle it)
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone Phone number (should be in international format)
     * @return bool
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        // Indonesian phone numbers: 62 followed by 8 and 7-11 more digits
        return preg_match('/^628[0-9]{7,11}$/', $phone) === 1;
    }

    /**
     * Send order created notification
     *
     * @param \App\Models\Order $order
     * @return array
     */
    public function sendOrderCreatedNotification($order): array
    {
        $message = "🎉 *Pesanan Berhasil Dibuat!*\n\n";
        $message .= "Halo {$order->user->name},\n\n";
        $message .= "Pesanan Anda telah berhasil dibuat:\n";
        $message .= "📋 No. Pesanan: *{$order->order_number}*\n";
        $message .= "💰 Total: *Rp " . number_format($order->total, 0, ',', '.') . "*\n";
        $message .= "📅 Tanggal: " . $order->created_at->format('d M Y, H:i') . "\n\n";
        $message .= "Silakan lakukan pembayaran untuk melanjutkan.\n\n";
        $message .= "Terima kasih telah menggunakan BundaGaya! 🙏";

        return $this->sendMessage($order->user->phone, $message);
    }

    /**
     * Send order status changed notification
     *
     * @param \App\Models\Order $order
     * @param string $statusLabel
     * @return array
     */
    public function sendOrderStatusChangedNotification($order, string $statusLabel): array
    {
        $emoji = $this->getStatusEmoji($order->status);
        
        $message = "{$emoji} *Status Pesanan Diperbarui*\n\n";
        $message .= "Halo {$order->user->name},\n\n";
        $message .= "Status pesanan Anda telah berubah:\n";
        $message .= "📋 No. Pesanan: *{$order->order_number}*\n";
        $message .= "📌 Status Baru: *{$statusLabel}*\n\n";
        
        $message .= $this->getStatusMessage($order->status);
        
        $message .= "\n\nCek detail pesanan di aplikasi BundaGaya.\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($order->user->phone, $message);
    }

    /**
     * Send payment received notification
     *
     * @param \App\Models\Order $order
     * @param \App\Models\Payment $payment
     * @return array
     */
    public function sendPaymentReceivedNotification($order, $payment): array
    {
        $message = "✅ *Pembayaran Diterima!*\n\n";
        $message .= "Halo {$order->user->name},\n\n";
        $message .= "Pembayaran Anda telah kami terima:\n";
        $message .= "📋 No. Pesanan: *{$order->order_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($payment->amount, 0, ',', '.') . "*\n";
        $message .= "💳 Metode: " . ucfirst(str_replace('_', ' ', $payment->method)) . "\n";
        $message .= "📅 Tanggal: " . $payment->paid_at->format('d M Y, H:i') . "\n\n";
        $message .= "Pesanan Anda sedang diproses oleh tim BundaGaya.\n\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($order->user->phone, $message);
    }

    /**
     * Send withdrawal approved notification
     *
     * @param \App\Models\Withdrawal $withdrawal
     * @return array
     */
    public function sendWithdrawalApprovedNotification($withdrawal): array
    {
        $message = "💰 *Penarikan Dana Disetujui*\n\n";
        $message .= "Halo {$withdrawal->user->name},\n\n";
        $message .= "Permintaan penarikan dana Anda telah disetujui:\n";
        $message .= "📋 No. Penarikan: *{$withdrawal->withdrawal_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($withdrawal->amount, 0, ',', '.') . "*\n";
        $message .= "🏦 Bank: {$withdrawal->bank_name}\n";
        $message .= "💳 No. Rekening: {$withdrawal->bank_account}\n\n";
        $message .= "Dana akan ditransfer dalam 1-3 hari kerja.\n\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($withdrawal->user->phone, $message);
    }

    /**
     * Send withdrawal rejected notification
     *
     * @param \App\Models\Withdrawal $withdrawal
     * @return array
     */
    public function sendWithdrawalRejectedNotification($withdrawal): array
    {
        $message = "❌ *Penarikan Dana Ditolak*\n\n";
        $message .= "Halo {$withdrawal->user->name},\n\n";
        $message .= "Mohon maaf, permintaan penarikan dana Anda ditolak:\n";
        $message .= "📋 No. Penarikan: *{$withdrawal->withdrawal_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($withdrawal->amount, 0, ',', '.') . "*\n\n";
        
        if ($withdrawal->rejection_reason) {
            $message .= "📝 Alasan:\n{$withdrawal->rejection_reason}\n\n";
        }
        
        $message .= "Dana tetap tersedia di saldo toko Anda.\n\n";
        $message .= "Jika ada pertanyaan, hubungi kami di support@bundagaya.com";

        return $this->sendMessage($withdrawal->user->phone, $message);
    }

    public function sendPayoutRequestNotification($payout): array
    {
        $message = "📋 *Permintaan Penarikan Saldo*\n\n";
        $message .= "Halo {$payout->user->name},\n\n";
        $message .= "Permintaan penarikan saldo Anda telah diajukan:\n";
        $message .= "📋 No. Penarikan: *{$payout->payout_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($payout->amount, 0, ',', '.') . "*\n";
        $message .= "🏦 Bank: {$payout->bank_name}\n";
        $message .= "💳 No. Rekening: {$payout->bank_account_number}\n\n";
        $message .= "Permintaan akan diproses oleh tim BundaGaya.\n\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($payout->user->phone, $message);
    }

    public function sendPayoutApprovedNotification($payout): array
    {
        $message = "✅ *Penarikan Saldo Disetujui*\n\n";
        $message .= "Halo {$payout->user->name},\n\n";
        $message .= "Permintaan penarikan saldo Anda telah disetujui:\n";
        $message .= "📋 No. Penarikan: *{$payout->payout_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($payout->amount, 0, ',', '.') . "*\n";
        $message .= "🏦 Bank: {$payout->bank_name}\n";
        $message .= "💳 No. Rekening: {$payout->bank_account_number}\n\n";
        $message .= "Dana akan ditransfer ke rekening Anda.\n\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($payout->user->phone, $message);
    }

    public function sendPayoutRejectedNotification($payout): array
    {
        $message = "❌ *Penarikan Saldo Ditolak*\n\n";
        $message .= "Halo {$payout->user->name},\n\n";
        $message .= "Mohon maaf, permintaan penarikan saldo Anda ditolak:\n";
        $message .= "📋 No. Penarikan: *{$payout->payout_number}*\n";
        $message .= "💰 Jumlah: *Rp " . number_format($payout->amount, 0, ',', '.') . "*\n\n";

        if ($payout->notes) {
            $message .= "📝 Alasan:\n{$payout->notes}\n\n";
        }

        $message .= "Saldo tetap tersedia di akun Anda.\n\n";
        $message .= "Jika ada pertanyaan, hubungi kami di support@bundagaya.com";

        return $this->sendMessage($payout->user->phone, $message);
    }

    /**
     * Get emoji for order status
     *
     * @param string $status
     * @return string
     */
    private function getStatusEmoji(string $status): string
    {
        return match($status) {
            'pending_payment' => '⏳',
            'paid' => '✅',
            'processing' => "📦",
            'shipped' => '🚚',
            'in_use' => '👗',
            'returned' => '↩️',
            'completed' => '🎉',
            'cancelled' => '❌',
            default => '📌',
        };
    }

    /**
     * Get message for order status
     *
     * @param string $status
     * @return string
     */
    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'paid' => "Pembayaran telah dikonfirmasi. Pesanan sedang diproses oleh tim BundaGaya.",
            'processing' => "Pesanan Anda sedang diproses dan akan segera dikirim.",
            'shipped' => "Produk telah dikirim. Silakan pantau status pengiriman di aplikasi.",
            'returned' => "Produk telah dikembalikan. Terima kasih!",
            'completed' => "Pesanan telah selesai. Terima kasih telah menggunakan BundaGaya!",
            'cancelled' => "Pesanan telah dibatalkan. Dana akan dikembalikan.",
            default => "Status pesanan telah diperbarui.",
        };
    }

    /**
     * Log WhatsApp message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logMessages) {
            Log::log($level, "WhatsApp: {$message}", $context);
        }
    }

    /**
     * Check if service is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }
}
