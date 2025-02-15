<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('carts', 'session_id')) {
            return;
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->string('session_id')->default('')->after('customer_id');
            $table->json('guest_shipping_address')->nullable()->after('shipping_address_id');
            $table->json('guest_payment_address')->nullable()->after('payment_address_id');
        });
        Schema::table('cart_products', function (Blueprint $table) {
            $table->string('session_id')->default('')->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('carts', 'extra')) {
            return;
        }
    }
};
