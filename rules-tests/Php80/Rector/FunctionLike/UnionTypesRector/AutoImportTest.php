<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AutoImportTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImport');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule_auto_import.php';
    }
}