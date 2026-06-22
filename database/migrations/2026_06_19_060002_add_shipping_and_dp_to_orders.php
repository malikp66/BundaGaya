<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('dp_total', 12, 2)->default(0)->after('total');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('dp_total');
            $table->decimal('grand_total', 12, 2)->default(0)->after('shipping_cost');
            $table->string('shipping_courier')->nullable()->after('grand_total');
            $table->string('shipping_service')->nullable()->after('shipping_courier');
            $table->string('tracking_number')->nullable()->after('shipping_service');
            $table->string('shipping_address')->nullable()->after('tracking_number');
            $table->string('city')->nullable()->after('shipping_address');
            $table->string('province')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('province');
            $table->string('district')->nullable()->after('postal_code');
            $table->string('suburb')->nullable()->after('district');
            $table->timestamp('processed_at')->nullable()->after('paid_at');
            $table->timestamp('shipped_at')->nullable()->after('processed_at');
            $table->timestamp('returned_at')->nullable()->after('cancelled_at');
            $table->decimal('dp_refunded', 12, 2)->default(0)->after('returned_at');
            $table->decimal('dp_deducted', 12, 2)->default(0)->after('dp_refunded');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'dp_total', 'shipping_cost', 'grand_total',
                'shipping_courier', 'shipping_service', 'tracking_number',
                'shipping_address', 'city', 'province', 'postal_code', 'district', 'suburb',
                'processed_at', 'shipped_at', 'returned_at',
                'dp_refunded', 'dp_deducted',
            ]);
        });
    }
};
