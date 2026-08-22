<?php

namespace App\Validator;

use App\Dto\FinalUserRecDto;

class UserValidator
{
    public function formatName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    public function formatEmail(string $email): string
    {
        return mb_strtolower(trim($email), 'UTF-8');
    }

    public function isValidEmail(string $email): bool
    {
        if ($email === '') {
            return false;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if (substr_count($email, '@') !== 1) {
            return false;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            return false;
        }

        if (strpos($parts[1], '.') === false) {
            return false;
        }

        return true;
    }

    /**
     * Process and validate a list of raw rows.
     *
     * @param array $rawRows Array of ['name' => ..., 'surname' => ..., 'email' => ...]
     * @param array $existingDbEmails Array of lowercase emails already in the database
     * @return FinalUserRecDto[]
     */
    public function validateBatch(array $rawRows, array $existingDbEmails = []): array
    {
        $records = [];
        $seenEmailsInBatch = [];
        $existingDbEmailsSet = array_flip(array_map('strtolower', $existingDbEmails));

        foreach ($rawRows as $index => $row) {
            $rawName = $row['name'] ?? '';
            $rawSurname = $row['surname'] ?? '';
            $rawEmail = $row['email'] ?? '';

            $formattedName = $this->formatName($rawName);
            $formattedSurname = $this->formatName($rawSurname);
            $formattedEmail = $this->formatEmail($rawEmail);

            $errors = [];

            if ($rawName === '') {
                $errors[] = 'Missing name';
            }

            if ($rawSurname === '') {
                $errors[] = 'Missing surname';
            }

            if ($rawEmail === '') {
                $errors[] = 'Missing email address';
            } elseif (!$this->isValidEmail($formattedEmail)) {
                $errors[] = 'Invalid email address format';
            } else {
                if (isset($seenEmailsInBatch[$formattedEmail])) {
                    $errors[] = 'Duplicate email address in file';
                } else {
                    $seenEmailsInBatch[$formattedEmail] = true;
                }

                if (isset($existingDbEmailsSet[$formattedEmail])) {
                    $errors[] = 'Email address already exists in database';
                }
            }

            $isValid = count($errors) === 0;

            $records[] = new FinalUserRecDto(
                $rawName,
                $rawSurname,
                $rawEmail,
                $formattedName,
                $formattedSurname,
                $formattedEmail,
                $isValid,
                $errors
            );
        }

        return $records;
    }
}
