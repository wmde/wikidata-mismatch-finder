<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class RenameMismatchStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (App::environment() == 'testing') {
            // SQLite doesn't support renaming or dropping columns,
            // so for testing we re-do the entire table
            $this->dropAndRecreateMismatchesTable();
        } else {
            // Laravel doesn't support renaming enum columns,
            // so we drop and re-create the entire column
            $this->dropAndRecreateColumn();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (App::environment() == 'testing') {
            $this->dropAndRestoreMismatchesTable();
        } else {
            $this->dropAndRestoreColumn();
        }
    }

    private function dropAndRecreateMismatchesTable() {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->drop();
        });

        Schema::create('mismatches', function (Blueprint $table) {
            $table->id();
            $table->string('statement_guid');
            $table->string('item_id');
            $table->string('property_id');
            $table->string('wikidata_value', 2048); // Arbitrary length above 1500 chars, upper limit for wikidata value
            $table->string('external_value', 2048); // Arbitrary length above 1500 chars, upper limit for external value
            $table->string('external_url', 2048)->nullable(); // Arbitrary length above 1500 chars, upper limit for url
            $table->enum('review_status', [
                'pending',
                'wikidata',
                'external',
                'both',
                'none'
            ])->default('pending');
            $table->foreignId('import_id')->constrained('import_meta');
            $table->timestamps();
        });
    }

    private function dropAndRecreateColumn() {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('review_status', [
                'pending',
                'wikidata',
                'external',
                'both',
                'none'
            ])->after('external_url')->default('pending');
        });
    }

    private function dropAndRestoreMismatchesTable() {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->drop();
        });

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
            ])->default('pending');
            $table->foreignId('import_id')->constrained('import_meta');
            $table->timestamps();
        });
    }

    private function dropAndRestoreColumn() {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->dropColumn('review_status');
            $table->enum('status', [
                'pending',
                'wikidata',
                'external',
                'both',
                'none'
            ])->after('external_url')->default('pending');
        });
    }
}
