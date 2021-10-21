<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalSourceToImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_meta', function (Blueprint $table) {
            $table->after('description', function(Blueprint $table) {
                $table->string('external_source', 255)->default('internet');
                $table->string('external_source_url', 2048)->nullable();
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
        Schema::table('import_meta', function (Blueprint $table) {
            $table->dropColumn('external_source');
            $table->dropColumn('external_source_url');
        });
    }
}
