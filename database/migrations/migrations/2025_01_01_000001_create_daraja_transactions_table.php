<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daraja_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // STK, C2B, B2C, etc.
            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('account_reference')->nullable();
            $table->string('transaction_desc')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
            
            $table->index('merchant_request_id');
            $table->index('checkout_request_id');
            $table->index('mpesa_receipt_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daraja_transactions');
    }
};