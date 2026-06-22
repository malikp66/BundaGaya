<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('shop_id')->constrained()->nullOnDelete();
            $table->decimal('suggested_price', 12, 2)->nullable()->after('price_per_day');
            $table->decimal('dp_percentage', 5, 2)->default(20.00)->after('is_featured');
            $table->decimal('weight', 8, 2)->nullable()->after('dp_percentage');
            $table->decimal('length', 8, 2)->nullable()->after('weight');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['suggested_price', 'dp_percentage', 'weight', 'length', 'width', 'height']);
        });
    }
};
