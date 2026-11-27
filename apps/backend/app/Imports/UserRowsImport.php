<?php

namespace App\Imports;

use App\Models\UserImport;
use App\Services\Users\UserImportRowService;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use SensitiveParameter;

class UserRowsImport implements OnEachRow, SkipsEmptyRows, WithChunkReading, WithHeadingRow
{
    /**
     * @var array<string, true>
     */
    private array $seen = [];

    public function __construct(
        private readonly UserImport $import,
        private readonly UserImportRowService $rows,
        #[SensitiveParameter] private readonly string $unusablePassword,
    ) {}

    public function onRow(Row $row): void
    {
        $result = $this->rows->process(
            $row->getIndex(),
            $row->toArray(formatData: false),
            $this->unusablePassword,
            $this->seen,
        );

        $this->rows->record($this->import, $result);
    }

    public function chunkSize(): int
    {
        return IMPORT_CHUNK_SIZE;
    }
}
