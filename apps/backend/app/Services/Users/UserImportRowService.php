<?php

namespace App\Services\Users;

use App\Actions\Users\CreateUserAction;
use App\Data\Users\ImportRowResult;
use App\Data\Users\NewUserData;
use App\Models\Person;
use App\Models\UserImport;
use App\Models\UserImportRow;
use App\Rules\UserRules;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SensitiveParameter;

class UserImportRowService
{
    public function __construct(
        private readonly CreateUserAction $createUser,
        private readonly ImportRowNormalizer $normalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $rawRow
     * @param  array<string, true>  $seen
     */
    public function process(int $line, array $rawRow, #[SensitiveParameter] string $unusablePassword, array &$seen): ImportRowResult
    {
        $row = $this->normalizer->normalize($rawRow);
        $identification = $row['identification'];
        $duplicates = $this->duplicatedInFile($row, $seen);

        if ($duplicates !== []) {
            return ImportRowResult::failed($line, $identification, $duplicates);
        }

        if ($identification !== null && Person::withTrashed()->where('identification', $identification)->exists()) {
            return ImportRowResult::skipped($line, $identification, 'La identificación ya está registrada.');
        }

        $validator = Validator::make($row, UserRules::newAccount());

        if ($validator->fails()) {
            return ImportRowResult::failed($line, $identification, $validator->errors()->toArray());
        }

        $seen['identification:'.$identification] = true;
        $seen['username:'.Str::lower($row['username'])] = true;

        try {
            $user = $this->createUser->execute(new NewUserData(
                firstName: $row['first_name'],
                middleName: $row['middle_name'],
                firstSurname: $row['first_surname'],
                secondSurname: $row['second_surname'],
                identification: $identification,
                birthDate: $row['birth_date'],
                username: $row['username'],
                password: $unusablePassword,
            ));
        } catch (UniqueConstraintViolationException) {
            return ImportRowResult::skipped($line, $identification, 'Otro proceso registró este usuario al mismo tiempo.');
        }

        return ImportRowResult::created($line, $identification, $user->email);
    }

    public function record(UserImport $import, ImportRowResult $result): void
    {
        UserImportRow::create([
            'user_import_id' => $import->getKey(),
            'line' => $result->line,
            'status' => $result->status,
            'identification' => $result->identification === null ? null : Str::substr($result->identification, 0, IMPORT_ROW_IDENTIFICATION_MAX_LENGTH),
            'email' => $result->email,
            'errors' => $result->errors === [] ? null : $result->errors,
        ]);

        UserImport::query()->whereKey($import->getKey())->incrementEach([
            'total_rows' => 1,
            UserImport::counterFor($result->status) => 1,
        ]);
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  array<string, true>  $seen
     * @return array<string, list<string>>
     */
    private function duplicatedInFile(array $row, array $seen): array
    {
        $errors = [];

        if ($row['identification'] !== null && isset($seen['identification:'.$row['identification']])) {
            $errors['identification'] = ['La identificación está repetida en el archivo.'];
        }

        if ($row['username'] !== null && isset($seen['username:'.Str::lower($row['username'])])) {
            $errors['username'] = ['El usuario está repetido en el archivo.'];
        }

        return $errors;
    }
}
