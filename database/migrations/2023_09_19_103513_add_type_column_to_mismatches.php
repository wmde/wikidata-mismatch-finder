<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToMismatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->enum('type', [
                'statement',
                'qualifier'
            ])->default('statement');
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
            $table->dropColumn('type');
        });
    }
}
