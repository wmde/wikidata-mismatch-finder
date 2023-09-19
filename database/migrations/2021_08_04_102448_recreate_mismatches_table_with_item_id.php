<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateMismatchesTableWithItemId extends Migration
{
    /**
     * This migration intends to add an 'item_id' column to the mismatches table.
     * Since SQLite does not allow adding non-nullable columns without a default value,
     * the cleanest way is to drop the entire table and re-create it with the new column.
     * No mismatches will be harmed, if this is executed immediately after creating the
     * table using 2021_07_19_123858_create_mismatches_table.php
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('mismatches');

        Schema::create('mismatches', function (Blueprint $table) {
            $table->id();
            $table->string('statement_guid');
            $table->string('item_id');
            $table->string('property_id');
            $table->string('wikidata_value', 2048); // Arbitrary length above 1500 chars, upper limit for wikidata value
            $table->string('external_value', 2048); // Arbitrary length above 1500 chars, upper limit for external value
            $table->string('external_url', 2048)->nullable(); // Arbitrary length above 1500 chars, upper limit for url
            $table->enum('status', [
                'pending',
                'wikidata',
                'external',
                'both',
                'none'
            ]);
            $table->foreignId('import_id')->constrained('import_meta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
}
