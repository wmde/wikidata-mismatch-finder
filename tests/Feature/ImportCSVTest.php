<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportMeta;
use App\Jobs\ImportCSV;
use App\Models\User;
use App\Models\Mismatch;
use Throwable;

class ImportCSVTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Ensure import persists mismatches to empty database
     */
    public function test_creates_mismatches_in_empty_db(): void
    {
        $filename = 'creates-mismatches.csv';
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569","3 April 1934"
            ,"Q12138","1934-04-03","https://d-nb.info/gnd/119004453","statement"],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21","Q6581072"
            ,"","nonbinary","https://www.imdb.com/name/nm0328762/","statement"]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        $expected = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $lines);

        ImportCSV::dispatch($import);

        $this->assertDatabaseCount('mismatches', count($lines));
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $import->id
        ]);

        foreach ($expected as $mismatch) {
            $this->assertDatabaseHas('mismatches', $mismatch);
        }
    }

    /**
     * Ensure reupload doesn't import duplicated row with reviewed state
     */
    public function test_doesnt_import_mismatches_when_row_exists_and_is_reviewed(): void
    {
        $filename = 'creates-mismatches.csv';
        $user = User::factory()->uploader()->create();
        $reupload_import = ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);

        $already_in_db_import = ImportMeta::factory()->for($user);

        Mismatch::factory()
            ->for($already_in_db_import)->create([
                'statement_guid' => 'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5',
                'item_id' => 'Q184746',
                'property_id' => 'P569',
                'meta_wikidata_value' => 'Q12138',
                'wikidata_value' => '3 April 1934',
                'external_value' => '1934-04-03',
                'external_url' => 'https://d-nb.info/gnd/119004453',
                'review_status' => 'both',
                'type' => 'statement'
        ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569","3 April 1934"
            ,"Q12138","1934-04-03","https://d-nb.info/gnd/119004453","statement"],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21","Q6581072"
            ,"","nonbinary","https://www.imdb.com/name/nm0328762/","statement"]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        $expected = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $lines);

        ImportCSV::dispatch($reupload_import);

        $this->assertDatabaseCount('mismatches', count($lines));
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $reupload_import->id
        ]);

        foreach ($expected as $mismatch) {
            $this->assertDatabaseHas('mismatches', $mismatch);
        }
    }

    /**
     * Ensure reupload imports duplicated row if db row has review status = 'pending'
     */
    public function test_imports_mismatches_when_row_exists_and_review_status_is_pending(): void
    {
        $filename = 'creates-mismatches.csv';
        $user = User::factory()->uploader()->create();
        $reupload_import = ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);

        $already_in_db_import = ImportMeta::factory()->for($user)->create();

        Mismatch::factory()
            ->for($already_in_db_import)->create([
                'statement_guid' => 'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5',
                'item_id' => 'Q184746',
                'property_id' => 'P569',
                'meta_wikidata_value' => 'Q12138',
                'wikidata_value' => '3 April 1934',
                'external_value' => '1934-04-03',
                'external_url' => 'https://d-nb.info/gnd/119004453',
                'review_status' => 'pending',
                'type' => 'statement'
        ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569","3 April 1934"
            ,"Q12138","1934-04-03","https://d-nb.info/gnd/119004453","statement"],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21","Q6581072"
            ,"","nonbinary","https://www.imdb.com/name/nm0328762/","statement"]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        $expected = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $lines);

        ImportCSV::dispatch($reupload_import);

        $this->assertDatabaseCount('mismatches', count($lines) + 1);
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $already_in_db_import->id
        ]);

        foreach ($expected as $mismatch) {
            $this->assertDatabaseHas('mismatches', $mismatch);
        }
    }

    /**
     * Ensure reupload imports duplicated rows if uploaded by different users
     */
    public function test_imports_mismatch_row_from_different_users_only_when_not_pending_in_db(): void
    {
        $filename = 'creates-mismatches.csv';
        $user1 = User::factory()->uploader()->create();
        $user2 = User::factory()->uploader()->create();
        $reupload_import1 = ImportMeta::factory()->for($user1)->create([
            'filename' => $filename
        ]);
        $reupload_import2 = ImportMeta::factory()->for($user2)->create([
            'filename' => $filename
        ]);

        $already_in_db_import = ImportMeta::factory()->for($user1)->create();

        Mismatch::factory()
            ->for($already_in_db_import)->create([
                'statement_guid' => 'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5',
                'item_id' => 'Q184746',
                'property_id' => 'P569',
                'meta_wikidata_value' => 'Q12138',
                'wikidata_value' => '3 April 1934',
                'external_value' => '1934-04-03',
                'external_url' => 'https://d-nb.info/gnd/119004453',
                'review_status' => 'both',
                'type' => 'statement'
        ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569","3 April 1934"
            ,"Q12138","1934-04-03","https://d-nb.info/gnd/119004453","statement"],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21","Q6581072"
            ,"","nonbinary","https://www.imdb.com/name/nm0328762/","statement"]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        $expected = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $lines);

        ImportCSV::dispatch($reupload_import1);
        ImportCSV::dispatch($reupload_import2);

        // total would be 5 but one is not imported because it's already reviewed in db
        $this->assertDatabaseCount('mismatches', count($lines) * 2);
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $reupload_import1->id,
            'import_id' => $reupload_import2->id,
        ]);

        foreach ($expected as $mismatch) {
            $this->assertDatabaseHas('mismatches', $mismatch);
        }
    }

    /**
     * Ensure no change is commited to Database in case of error
     */
    public function test_rolls_back_on_failure(): void
    {
        $filename = 'fails-gracefully.csv';
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569"
            ,"3 April 1934","Q12138","1934-04-03","https://d-nb.info/gnd/119004453"],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21"
            ,"Q6581072","","nonbinary"]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        try {
            ImportCSV::dispatch($import);
        } catch (Throwable $ignored) {
            $this->assertDatabaseHas('import_meta', [
                'id' => $import->id,
                'status' => 'failed'
            ]);
            $this->assertDatabaseHas('import_failures', [
                'id' => $import->id
            ]);
            $this->assertDatabaseCount('mismatches', 0);
            $this->assertDatabaseMissing('mismatches', [
                'import_id' => $import->id
            ]);
        }
    }

    /**
     * Ensure reupload doesn't import duplicated row with reviewed state and ignoring type column if it's empty
     */
    public function test_doesnt_import_mismatches_when_row_exists_and_is_reviewed_and_type_is_empty_in_csv(): void
    {
        $filename = 'creates-mismatches.csv';
        $user1 = User::factory()->uploader()->create();
        $user2 = User::factory()->uploader()->create();
        $reupload_import1 = ImportMeta::factory()->for($user1)->create([
            'filename' => $filename
        ]);
        $reupload_import2 = ImportMeta::factory()->for($user2)->create([
            'filename' => $filename
        ]);

        $already_in_db_import = ImportMeta::factory()->for($user1)->create();

        Mismatch::factory()
            ->for($already_in_db_import)->create([
                'statement_guid' => 'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5',
                'item_id' => 'Q184746',
                'property_id' => 'P569',
                'meta_wikidata_value' => 'Q12138',
                'wikidata_value' => '3 April 1934',
                'external_value' => '1934-04-03',
                'external_url' => 'https://d-nb.info/gnd/119004453',
                'review_status' => 'both',
                'type' => 'statement'
            ]);

        $header = config('imports.upload.column_keys');
        $lines = [
            ["Q184746","Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5","P569","3 April 1934"
                ,"Q12138","1934-04-03","https://d-nb.info/gnd/119004453",""],
            ["Q184746","Q184746$7200D1AD-E4E8-401B-8D57-8C823810F11F","P21","Q6581072"
                ,"","nonbinary","https://www.imdb.com/name/nm0328762/",""]
        ];

        $content = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$header], $lines)));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            $content
        );

        $expected = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $lines);

        ImportCSV::dispatch($reupload_import1);
        ImportCSV::dispatch($reupload_import2);

        // total would be 5 but one is not imported because it's already reviewed in db
        $this->assertDatabaseCount('mismatches', count($lines) * 2);
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $reupload_import1->id,
            'import_id' => $reupload_import2->id,
        ]);

        foreach ($expected as $mismatch) {
            $this->assertDatabaseHas('mismatches', $mismatch);
        }
    }
}
