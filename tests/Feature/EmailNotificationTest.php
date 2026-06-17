<?php

namespace Tests\Feature;

use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\ShopApprovedMail;
use App\Mail\ShopRejectedMail;
use App\Mail\WithdrawalApprovedMail;
use App\Mail\WithdrawalRejectedMail;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $shop;
    protected $product;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        Setting::create([
            'key' => 'admin_fee',
            'value' => '5000',
            'type' => 'integer',
        ]);

        $this->user = User::factory()->create(['role' => 'customer']);
        $shopOwner = User::factory()->create(['role' => 'shop_owner']);
        $this->shop = Shop::factory()->create([
            'user_id' => $shopOwner->id,
            'status' => 'active',
            'commission_rate' => 10,
        ]);

        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price_per_day' => 100000,
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_order_created_email_is_sent()
    {
        $notificationService = app(NotificationService::class);
        $notificationService->sendOrderCreatedNotification($this->order);

        Mail::assertSent(OrderCreatedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_order_status_changed_email_is_sent()
    {
        $this->order->update(['status' => 'paid']);

        $notificationService = app(NotificationService::class);
        $notificationService->sendOrderStatusChangedNotification($this->order);

        Mail::assertSent(OrderStatusChangedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_payment_received_email_is_sent()
    {
        $payment = Payment::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'paid',
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->sendPaymentReceivedNotification($this->order, $payment);

        Mail::assertSent(PaymentReceivedMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_shop_approved_email_is_sent()
    {
        $notificationService = app(NotificationService::class);
        $notificationService->sendShopApprovedNotification($this->shop);

        Mail::assertSent(ShopApprovedMail::class, function ($mail) {
            return $mail->hasTo($this->shop->user->email);
        });
    }

    public function test_shop_rejected_email_is_sent()
    {
        $this->shop->update([
            'status' => 'rejected',
            'rejection_reason' => 'Incomplete documentation',
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->sendShopRejectedNotification($this->shop);

        Mail::assertSent(ShopRejectedMail::class, function ($mail) {
            return $mail->hasTo($this->shop->user->email);
        });
    }

    public function test_withdrawal_approved_email_is_sent()
    {
        $withdrawal = \App\Models\Withdrawal::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->shop->user_id,
            'status' => 'approved',
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->sendWithdrawalApprovedNotification($withdrawal);

        Mail::assertSent(WithdrawalApprovedMail::class, function ($mail) {
            return $mail->hasTo($this->shop->user->email);
        });
    }

    public function test_withdrawal_rejected_email_is_sent()
    {
        $withdrawal = \App\Models\Withdrawal::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->shop->user_id,
            'status' => 'rejected',
            'rejection_reason' => 'Insufficient balance',
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->sendWithdrawalRejectedNotification($withdrawal);

        Mail::assertSent(WithdrawalRejectedMail::class, function ($mail) {
            return $mail->hasTo($this->shop->user->email);
        });
    }

    public function test_order_created_email_contains_correct_data()
    {
        $notificationService = app(NotificationService::class);
        $notificationService->sendOrderCreatedNotification($this->order);

        Mail::assertSent(OrderCreatedMail::class, function ($mail) {
            return $mail->order->id === $this->order->id &&
                   $mail->hasTo($this->user->email);
        });
    }

    public function test_multiple_status_changes_send_multiple_emails()
    {
        $notificationService = app(NotificationService::class);

        $this->order->update(['status' => 'paid']);
        $notificationService->sendOrderStatusChangedNotification($this->order);

        $this->order->update(['status' => 'confirmed_by_owner']);
        $notificationService->sendOrderStatusChangedNotification($this->order);

        $this->order->update(['status' => 'completed']);
        $notificationService->sendOrderStatusChangedNotification($this->order);

        Mail::assertSent(OrderStatusChangedMail::class, 3);
    }
}
