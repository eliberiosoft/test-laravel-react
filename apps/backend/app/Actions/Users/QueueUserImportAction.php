<?php

namespace App\Actions\Users;

use App\Jobs\ProcessUserImportJob;
use App\Models\User;
use App\Models\UserImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class QueueUserImportAction
{
    public function execute(UploadedFile $file, User $requestedBy): UserImport
    {
        $path = $file->storeAs(IMPORT_STORAGE_DIRECTORY, Str::uuid().'.'.Str::lower($file->getClientOriginalExtension()), 'local');

        try {
            return DB::transaction(function () use ($file, $requestedBy, $path): UserImport {
                $import = UserImport::create([
                    'user_id' => $requestedBy->getKey(),
                    'file_name' => Str::substr($file->getClientOriginalName(), 0, IMPORT_FILE_NAME_MAX_LENGTH),
                    'file_path' => $path,
                    'file_hash' => hash_file('sha256', $file->getRealPath()),
                ]);

                ProcessUserImportJob::dispatch($import)
                    ->onConnection(config('accounts.import_queue_connection'))
                    ->onQueue(IMPORT_QUEUE)
                    ->afterCommit();

                return $import;
            }, DB_TRANSACTION_ATTEMPTS);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }
}
