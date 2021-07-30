<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ImportMeta;

class CreateMismatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mismatches', function (Blueprint $table) {
            $table->id();
            $table->string('statement_guid');
            $table->string('property_id');
            $table->string('wikidata_value', 2048);
            $table->string('external_value', 2048);
            $table->string('external_url', 2048)->nullable();
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
        Schema::dropIfExists('mismatches');
    }
}
