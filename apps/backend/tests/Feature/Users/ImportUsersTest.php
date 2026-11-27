<?php

namespace Tests\Feature\Users;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\ProcessUserImportAction;
use App\Data\Users\NewUserData;
use App\Enums\ImportStatus;
use App\Enums\RoleName;
use App\Jobs\ProcessUserImportJob;
use App\Models\User;
use App\Models\UserImport;
use App\Services\Users\EmailAddressService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class ImportUsersTest extends TestCase
{
    use RefreshDatabase;

    private const string HEADER = 'first_name,middle_name,first_surname,second_surname,identification,birth_date,username';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::Admin->value));
    }

    public function test_upload_answers_immediately_and_queues_the_processing(): void
    {
        Queue::fake();

        $response = $this->upload(['Juan,,Piguave,Loor,1203574901,,Jpiguave01'])
            ->assertAccepted()
            ->assertJsonPath('data.status', ImportStatus::Pending->value);

        $import = UserImport::query()->sole();
        $response->assertHeader('Location', route('users.imports.show', $import));
        Storage::disk('local')->assertExists($import->file_path);
        Queue::assertPushedOn(IMPORT_QUEUE, ProcessUserImportJob::class);
    }

    public function test_processed_import_reports_each_row_and_discards_the_file(): void
    {
        $existing = User::factory()->create();

        $id = $this->upload([
            'Juan,Alberto,Piguave,Loor,1203574901,1992-07-21,Jpiguave01',
            'Ana,,Torres,,0923456781,,sinmayuscula1',
            'Pedro,,Piguave,Loor,1203574901,,Ppiguave01',
            "Luis,,Mora,,{$existing->person->identification},,Lmora0001",
        ])->json('data.id');

        $this->getJson("/api/users/imports/{$id}")
            ->assertOk()
            ->assertJsonPath('data.status', ImportStatus::Completed->value)
            ->assertJsonPath('data.summary', ['total' => 4, 'created' => 1, 'skipped' => 1, 'failed' => 2]);

        $this->getJson("/api/users/imports/{$id}/rows?filter[status]=failed")
            ->assertOk()
            ->assertJsonPath('data.*.line', [3, 4])
            ->assertJsonPath('data.1.errors.identification.0', 'La identificación está repetida en el archivo.');

        $this->getJson("/api/users/imports/{$id}/rows?filter[status]=created")
            ->assertJsonPath('data.0.email', 'jpiguavel@mail.com');

        $this->assertNull(UserImport::query()->findOrFail($id)->file_path);
        $this->assertSame([], Storage::disk('local')->allFiles(IMPORT_STORAGE_DIRECTORY));
    }

    public function test_reprocessing_the_same_file_does_not_create_duplicates(): void
    {
        $rows = ['Juan,,Piguave,Loor,1203574901,,Jpiguave01', 'Ana,,Torres,Vera,0923456781,,Atorres001'];

        $this->upload($rows);
        $second = $this->upload($rows)->json('data.id');

        $this->getJson("/api/users/imports/{$second}")
            ->assertJsonPath('data.summary', ['total' => 2, 'created' => 0, 'skipped' => 2, 'failed' => 0]);
        $this->assertSame(3, User::count());
    }

    public function test_csv_without_trailing_newline_is_processed_and_txt_files_are_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, implode("\n", [self::HEADER, 'Juan,,Piguave,Loor,1203574901,,Jpiguave01']));
        $csv = new UploadedFile($path, 'users.csv', null, null, true);

        $this->assertSame('text/plain', $csv->getMimeType());

        $id = $this->postJson('/api/users/imports', ['file' => $csv])->assertAccepted()->json('data.id');

        $this->getJson("/api/users/imports/{$id}")
            ->assertJsonPath('data.status', ImportStatus::Completed->value)
            ->assertJsonPath('data.summary.created', 1);

        $txt = UploadedFile::fake()->createWithContent('users.txt', self::HEADER);
        $this->postJson('/api/users/imports', ['file' => $txt])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_an_oversized_cell_fails_its_row_without_aborting_the_import(): void
    {
        $id = $this->upload([
            'Juan,,Piguave,Loor,'.str_repeat('9', 60).',,Jpiguave01',
            'Ana,,Torres,Vera,0923456781,,Atorres001',
        ])->json('data.id');

        $this->getJson("/api/users/imports/{$id}")
            ->assertJsonPath('data.status', ImportStatus::Completed->value)
            ->assertJsonPath('data.summary', ['total' => 2, 'created' => 1, 'skipped' => 0, 'failed' => 1]);
    }

    public function test_rows_saved_before_an_unexpected_error_are_kept(): void
    {
        Queue::fake();
        $import = UserImport::query()->findOrFail($this->upload([
            'Juan,,Piguave,Loor,1203574901,,Jpiguave01',
            'Ana,,Torres,Vera,0923456781,,Atorres001',
            'Luis,,Mora,Paz,0956781234,,Lmora0001',
        ])->json('data.id'));

        $this->app->bind(CreateUserAction::class, fn ($app) => new class($app->make(EmailAddressService::class)) extends CreateUserAction
        {
            public function execute(NewUserData $data): User
            {
                throw_if($data->identification === '0956781234', RuntimeException::class, 'Unexpected failure');

                return parent::execute($data);
            }
        });

        try {
            app(ProcessUserImportAction::class)->execute($import);
            $this->fail('The import should have stopped on the unexpected error.');
        } catch (RuntimeException) {
        }

        $this->assertSame(2, User::query()->whereIn('username', ['Jpiguave01', 'Atorres001'])->count());
    }

    public function test_corrupt_file_marks_the_import_as_failed_through_the_queue(): void
    {
        Storage::disk('local')->put('imports/corrupt.xlsx', random_bytes(256));
        $import = UserImport::create([
            'user_id' => auth()->id(),
            'file_name' => 'users.xlsx',
            'file_path' => 'imports/corrupt.xlsx',
            'file_hash' => str_repeat('0', 64),
        ]);

        try {
            app('queue')->connection('sync')->push(new ProcessUserImportJob($import));
            $this->fail('The corrupt file should have failed the job.');
        } catch (Throwable) {
        }

        $this->getJson("/api/users/imports/{$import->id}")
            ->assertJsonPath('data.status', ImportStatus::Failed->value)
            ->assertJsonPath('data.error', 'No se pudo procesar el archivo.');
        Storage::disk('local')->assertMissing('imports/corrupt.xlsx');
    }

    public function test_long_file_names_are_accepted_and_shortened(): void
    {
        Queue::fake();
        $file = UploadedFile::fake()->createWithContent(str_repeat('a', 300).'.csv', self::HEADER);

        $this->postJson('/api/users/imports', ['file' => $file])->assertAccepted();

        $this->assertSame(IMPORT_FILE_NAME_MAX_LENGTH, mb_strlen(UserImport::query()->sole()->file_name));
    }

    public function test_imports_are_only_visible_to_the_admin_who_uploaded_them(): void
    {
        Queue::fake();
        $id = $this->upload(['Juan,,Piguave,Loor,1203574901,,Jpiguave01'])->json('data.id');

        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::Admin->value));
        $this->getJson("/api/users/imports/{$id}")->assertForbidden();
        $this->getJson("/api/users/imports/{$id}/rows")->assertForbidden();

        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::User->value));
        $this->upload(['Ana,,Torres,Vera,0923456781,,Atorres001'])->assertForbidden();
    }

    /**
     * @param  list<string>  $rows
     */
    private function upload(array $rows): TestResponse
    {
        $file = UploadedFile::fake()->createWithContent('users.csv', implode("\n", [self::HEADER, ...$rows]));

        return $this->postJson('/api/users/imports', ['file' => $file]);
    }
}
