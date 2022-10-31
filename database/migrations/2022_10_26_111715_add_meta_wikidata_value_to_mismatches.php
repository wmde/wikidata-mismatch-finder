<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaWikidataValueToMismatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->after('wikidata_value', function(Blueprint  $table) {
               $table->string('meta_wikidata_value')->nullable();
            });
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
            $table->dropColumn('meta_wikidata_value');
        });
    }
}
