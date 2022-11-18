<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMissingToMismatchesReviewStatus extends Migration
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
            // Laravel doesn't support updating enum columns,
            // so we modify the column with raw SQL
            $this->modifyColumn();
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
            $this->unmodifyColumn();
        }
    }

    private function dropAndRecreateMismatchesTable()
    {
        Schema::table('mismatches', function (Blueprint $table) {
            $table->drop();
        });

        Schema::create('mismatches', function (Blueprint $table) {
            $table->id();
            $table->string('statement_guid');
            $table->string('item_id');
            $table->string('property_id');
            $table->string('wikidata_value', 2048); // Arbitrary length above 1500 chars, upper limit for wikidata value
            $table->string('meta_wikidata_value')->nullable();
            $table->string('external_value', 2048); // Arbitrary length above 1500 chars, upper limit for external value
            $table->string('external_url', 2048)->nullable(); // Arbitrary length above 1500 chars, upper limit for url
            $table->enum('review_status', [
                'pending',
                'wikidata',
                'external',
                'both',
                'none',
                'missing'
            ])->default('pending');
            $table->foreignId('import_id')->constrained('import_meta');
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained('users');
        });
    }

    private function modifyColumn()
    {
        // add "missing" at the end of the enum (efficient in MySQL)
        DB::statement(
            'ALTER TABLE mismatches MODIFY COLUMN review_status ENUM('
            . '"pending", "wikidata", "external", "both", "none", "missing"'
            . ') NOT NULL DEFAULT "pending"'
        );
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
            $table->string('meta_wikidata_value')->nullable();
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
            $table->foreignId('user_id')->nullable()->constrained('users');
        });
    }

    private function unmodifyColumn()
    {
        // note: this will fail if there are any rows with "mismatch" review_status
        DB::statement(
            'ALTER TABLE mismatches MODIFY COLUMN review_status ENUM('
            . '"pending", "wikidata", "external", "both", "none"'
            . ') NOT NULL DEFAULT "pending"'
        );
    }
}
