<?php

namespace App\Services\Users;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportRowNormalizer
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string|null>
     */
    public function normalize(array $row): array
    {
        $normalized = [];

        foreach (IMPORT_COLUMNS as $column) {
            $normalized[$column] = match ($column) {
                'identification' => $this->identification($row[$column] ?? null),
                'birth_date' => $this->date($row[$column] ?? null),
                default => $this->text($row[$column] ?? null),
            };
        }

        return $normalized;
    }

    private function identification(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            return Str::padLeft((string) (int) $value, 10, '0');
        }

        return $this->text($value);
    }

    private function date(mixed $value): ?string
    {
        if (is_int($value) || is_float($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        return $this->text($value);
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
