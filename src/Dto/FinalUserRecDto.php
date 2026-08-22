<?php

namespace App\Dto;

class FinalUserRecDto
{
    private string $rawName;
    private string $rawSurname;
    private string $rawEmail;
    private string $formattedName;
    private string $formattedSurname;
    private string $formattedEmail;
    private bool $isValid;
    private array $errors;

    public function __construct(
        string $rawName,
        string $rawSurname,
        string $rawEmail,
        string $formattedName,
        string $formattedSurname,
        string $formattedEmail,
        bool $isValid = true,
        array $errors = []
    ) {
        $this->rawName = $rawName;
        $this->rawSurname = $rawSurname;
        $this->rawEmail = $rawEmail;
        $this->formattedName = $formattedName;
        $this->formattedSurname = $formattedSurname;
        $this->formattedEmail = $formattedEmail;
        $this->isValid = $isValid;
        $this->errors = $errors;
    }

    public function getRawName(): string
    {
        return $this->rawName;
    }

    public function getRawSurname(): string
    {
        return $this->rawSurname;
    }

    public function getRawEmail(): string
    {
        return $this->rawEmail;
    }

    public function getFormattedName(): string
    {
        return $this->formattedName;
    }

    public function getFormattedSurname(): string
    {
        return $this->formattedSurname;
    }

    public function getFormattedEmail(): string
    {
        return $this->formattedEmail;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
        $this->isValid = false;
    }

    public function getStatus(): string
    {
        return $this->isValid ? 'Valid' : 'Error';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->formattedName,
            'surname' => $this->formattedSurname,
            'email' => $this->formattedEmail,
            'raw_name' => $this->rawName,
            'raw_surname' => $this->rawSurname,
            'raw_email' => $this->rawEmail,
            'status' => $this->getStatus(),
            'is_valid' => $this->isValid,
            'errors' => $this->errors,
            'error_message' => implode('; ', $this->errors)
        ];
    }
}
