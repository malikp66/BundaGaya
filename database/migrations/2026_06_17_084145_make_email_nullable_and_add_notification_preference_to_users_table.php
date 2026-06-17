<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable
            $table->string('email')->nullable()->change();
            
            // Make phone required (not nullable)
            $table->string('phone')->nullable(false)->change();
            
            // Add notification preference field
            $table->string('notification_preference')->default('whatsapp')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert email to required
            $table->string('email')->nullable(false)->change();
            
            // Make phone nullable again
            $table->string('phone')->nullable()->change();
            
            // Drop notification preference field
            $table->dropColumn('notification_preference');
        });
    }
};
