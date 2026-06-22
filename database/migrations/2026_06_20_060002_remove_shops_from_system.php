<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
            $table->foreignId('user_id')->nullable()->constrained()->after('id');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_id');
        });

        Schema::dropIfExists('shops');
    }

    public function down(): void
    {
        //
    }
};
