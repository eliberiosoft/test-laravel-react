<?php

namespace App\Actions\Users;

use App\Enums\ImportStatus;
use App\Imports\UserRowsImport;
use App\Models\UserImport;
use App\Services\Users\UserImportRowService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Support\CauserResolver;

class ProcessUserImportAction
{
    public function __construct(
        private readonly UserImportRowService $rows,
        private readonly CauserResolver $causers,
    ) {}

    public function execute(UserImport $import): void
    {
        $import->forceFill(['status' => ImportStatus::Processing, 'started_at' => now()])->save();

        $this->causers->withCauser($import->user, fn () => Excel::import(
            new UserRowsImport($import, $this->rows, Hash::make(Str::password(IMPORT_UNUSABLE_PASSWORD_LENGTH))),
            $import->file_path,
            'local',
            $this->readerType($import->file_path),
        ));

        $import->refresh()->forceFill(['status' => ImportStatus::Completed, 'finished_at' => now()])->save();

        activity('users')
            ->causedBy($import->user)
            ->performedOn($import)
            ->event('imported')
            ->withProperties([
                'file' => $import->file_name,
                'sha256' => $import->file_hash,
                'total' => $import->total_rows,
                'created' => $import->created_rows,
                'skipped' => $import->skipped_rows,
                'failed' => $import->failed_rows,
            ])
            ->log('Carga masiva de usuarios');

        $this->discardFile($import);
    }

    public function markAsFailed(UserImport $import): void
    {
        $import->forceFill([
            'status' => ImportStatus::Failed,
            'error' => 'No se pudo procesar el archivo.',
            'finished_at' => now(),
        ])->save();

        $this->discardFile($import);
    }

    private function readerType(string $path): string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'xlsx' => ExcelFormat::XLSX,
            default => ExcelFormat::CSV,
        };
    }

    private function discardFile(UserImport $import): void
    {
        if ($import->file_path !== null) {
            Storage::disk('local')->delete($import->file_path);
            $import->forceFill(['file_path' => null])->save();
        }
    }
}
