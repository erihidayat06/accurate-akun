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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('no'); // Tidak unik, karena bisa punya banyak versi
            $table->bigInteger('item_id');
            $table->string('nama_barang');
            $table->decimal('qty', 15, 2);
            $table->string('st');
            $table->decimal('hb_baru', 15, 2);
            $table->decimal('disc_fp_baru', 15, 2)->nullable();
            $table->decimal('hb_lama', 15, 2)->nullable();
            $table->string('itemType')->nullable();
            $table->timestamp('tgl_transaksi')->nullable();

            for ($i = 1; $i <= 5; $i++) {
                $table->string("unit{$i}Name")->nullable();
                $table->decimal("hj_lama{$i}", 15, 2)->nullable();
                $table->decimal("hj_baru{$i}", 15, 2)->nullable();
                $table->string("st{$i}")->nullable();
                $table->string("rs{$i}")->nullable();
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
