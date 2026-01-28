<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->decimal('price_card', 10, 2)->nullable()->after('price');
            $table->decimal('price_pix', 10, 2)->nullable()->after('price_card');
        });

        // Migrate existing data: copy 'price' to both new fields
        DB::table('payment_batches')->update([
            'price_card' => DB::raw('price'),
            'price_pix' => DB::raw('price'),
        ]);

        // Make the new columns non-nullable
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->decimal('price_card', 10, 2)->nullable(false)->change();
            $table->decimal('price_pix', 10, 2)->nullable(false)->change();
        });

        // Drop the old 'price' column
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_batches', function (Blueprint $table) {
            // Re-add the old 'price' column
            $table->decimal('price', 10, 2)->after('event_id');
        });

        // Copy price_card to price (arbitrary choice)
        DB::table('payment_batches')->update([
            'price' => DB::raw('price_card'),
        ]);

        // Drop the new columns
        Schema::table('payment_batches', function (Blueprint $table) {
            $table->dropColumn(['price_card', 'price_pix']);
        });
    }
};
