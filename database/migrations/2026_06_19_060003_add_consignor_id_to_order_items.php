<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('consignor_id')->nullable()->after('shop_id')->constrained('users')->nullOnDelete();
            $table->decimal('dp_amount', 12, 2)->default(0)->after('net_amount');
            $table->decimal('dp_percentage', 5, 2)->default(20.00)->after('dp_amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consignor_id');
            $table->dropColumn(['dp_amount', 'dp_percentage']);
        });
    }
};
