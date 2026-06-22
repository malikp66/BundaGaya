<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->after('user_id');
            $table->string('instagram')->nullable()->after('customer_name');
            $table->date('return_date')->after('suburb');
            $table->string('refund_bank_name')->after('notes');
            $table->string('refund_bank_account')->after('refund_bank_name');
            $table->string('refund_bank_holder')->after('refund_bank_account');
            $table->string('dp_status')->default('pending')->after('dp_deducted');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'instagram',
                'return_date',
                'refund_bank_name',
                'refund_bank_account',
                'refund_bank_holder',
                'dp_status',
            ]);
        });
    }
};
