<?php

namespace Tests;

use App\Parser\CsvParser;
use App\Validator\UserValidator;
use App\Dto\FinalUserRecDto;
use PHPUnit\Framework\TestCase;

class UserImporterTest extends TestCase
{
    private UserValidator $validator;
    private CsvParser $parser;

    protected function setUp(): void
    {
        $this->validator = new UserValidator();
        $this->parser = new CsvParser();
    }

    public function testNameCapitalisation(): void
    {
        $this->assertEquals('John', $this->validator->formatName('john'));
        $this->assertEquals('Smith', $this->validator->formatName('SMITH'));
        $this->assertEquals('Mary Jane', $this->validator->formatName('mARy jAnE'));
    }

    public function testEmailLowercasing(): void
    {
        $this->assertEquals('john.smith@example.com', $this->validator->formatEmail('JOHN.SMITH@EXAMPLE.COM'));
    }

    public function testEmailValidation(): void
    {
        $this->assertTrue($this->validator->isValidEmail('user@example.com'));
        $this->assertFalse($this->validator->isValidEmail('invalid-email'));
        $this->assertFalse($this->validator->isValidEmail('missing@'));
        $this->assertFalse($this->validator->isValidEmail('bad@@example.com'));
    }

    public function testCsvParsingAndValidation(): void
    {
        $csv = "name,surname,email\njohn,smith,JOHN.SMITH@EXAMPLE.COM\nJane,Doe,invalid-email\n";
        $rows = $this->parser->parseString($csv);
        $records = $this->validator->validateBatch($rows);

        $this->assertCount(2, $records);

        $this->assertTrue($records[0]->isValid());
        $this->assertEquals('John', $records[0]->getFormattedName());
        $this->assertEquals('john.smith@example.com', $records[0]->getFormattedEmail());

        $this->assertFalse($records[1]->isValid());
        $this->assertContains('Invalid email address format', $records[1]->getErrors());
    }

    public function testDuplicateEmailDetection(): void
    {
        $csv = "name,surname,email\njohn,smith,john@example.com\nother,user,JOHN@EXAMPLE.COM\n";
        $rows = $this->parser->parseString($csv);
        $records = $this->validator->validateBatch($rows);

        $this->assertTrue($records[0]->isValid());
        $this->assertFalse($records[1]->isValid());
        $this->assertContains('Duplicate email address in file', $records[1]->getErrors());
    }

    public function testFileExtensionValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type. File must be a .csv file.');
        $this->parser->parseFile(__DIR__ . '/../composer.json');
    }

    public function testFileSizeValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File size exceeds the maximum limit of 50 MB.');
        $largeContent = str_repeat('a', 52428801);
        $this->parser->parseString($largeContent);
    }
}
