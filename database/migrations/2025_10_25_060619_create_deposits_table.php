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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('trading_account');
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->decimal('amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->string('currency');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing'])->default('pending');
            $table->string('payment_gateway');
            $table->string('payment_id')->nullable();
            $table->date('last_contact')->nullable();
            $table->string('offer')->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('account_manager')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
