<?php

namespace App\Jobs;

use App\Actions\Users\ProcessUserImportAction;
use App\Models\UserImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessUserImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = IMPORT_JOB_TIMEOUT;

    public function __construct(public readonly UserImport $import) {}

    public function handle(ProcessUserImportAction $process): void
    {
        $process->execute($this->import);
    }

    public function failed(?Throwable $exception): void
    {
        app(ProcessUserImportAction::class)->markAsFailed($this->import);
    }
}
