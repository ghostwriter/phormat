<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

trait One
{
    public function a(string $path): void
    {
    }
}

trait Three
{
    public function c(string $path): void
    {
    }
}

trait Two
{
    protected static function d(string $path): void
    {
    }

    private static function b(string $path): void
    {
    }
}

final class Runner2
{
    use One;
    use Three;
    use Two;

    public const string TEMP_PATH = __DIR__ . '/../../temp';

    private const string DEFAULT_PATH = __DIR__ . '/../oss/';

    private const string PATH = __DIR__ . '/../oss/';

    public int $count = 0;

    private int $total = -1;

    public function a(string $path): void
    {
    }

    public function c(string $path): void
    {
    }

    private function b(string $path): void
    {
    }

    //    public function run(string $path): void
    //    {
    //        foreach ($this->fileSystem->recursiveDirectoryIterator($path) as $file) {
    ////            if (! $this->formatter->matches($file)) {
    //                $this->formatter->format($file);
    ////            }
    //        }
    //        // $file = new SplFileInfo(__DIR__ . '/../src/Phormat.php');
    //        // $this->formatter->format($file);
    //    }
    protected static function d(string $path): void
    {
    }
}
