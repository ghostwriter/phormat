<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Closure;
use Composer\InstalledVersions;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ExceptionInterface as EventDispatcherExceptionInterface;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Ghostwriter\Phormat\Container\ServiceProvider;
use Ghostwriter\Phormat\EventDispatcher\Event\StartApplication;
use Ghostwriter\Phormat\Exception\ShouldNotHappenException;
use Ghostwriter\Phormat\Value\PhpFileFinder;
use InvalidArgumentException;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use SebastianBergmann\Diff\Differ as SebastianBergmannDiffer;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/** @see PhormatTest */
final readonly class Phormat implements PhormatInterface
{
    private const string PHORMAT = 'Phormat';

    private const string PHP_CODE_FORMATTER = 'PHP Code Formatter';

    private const string GHOSTWRITER_PHORMAT = 'ghostwriter/phormat';

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ContainerInterface $container,
        private FilesystemInterface $filesystem,
        private PhpFileFinder $phpFileFinder,
        private Parser $parser,
        private Standard $standard,
        private SebastianBergmannDiffer $sebastianBergmannDiffer,
        private ColorConsoleDiff $differ,
        private SymfonyStyle $symfonyStyle,
        private ColorConsoleDiff $colorConsoleDiff,
        private Runner $runner,
    ) {
    }

    public static function new(): self
    {
        \ini_set('memory_limit', '-1');

        $container = Container::getInstance();
        if (! $container->has(ServiceProvider::class)) {
            $container->provide(ServiceProvider::class);
        }

        return $container->get(self::class);
    }

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        /** @var array{w?:string,workspace?:string,dry-run?:bool} $options */
        $options = \getopt('w:', ['workspace:', 'dry-run']);

        $dryRun = \array_key_exists('dry-run', $options);

        $workspace = Workspace::new(
            $options['w'] ?? $options['workspace'] ?? $this->filesystem->currentWorkingDirectory(),
        );

        $this->eventDispatcher->dispatch(
            StartApplication::new(self::PHORMAT, self::PHP_CODE_FORMATTER, $this->version(), $workspace, $dryRun),
        );

        $vendorDirectory = $workspace->path('vendor');

        $config = $workspace->config($this->filesystem);

        foreach ($this->symfonyStyle->progressIterate($config->filesAndDirectories()) as $phpFile) {
            $path = $phpFile->path();
            if (\str_starts_with($path, $vendorDirectory)) {
                // skip vendor directory
                continue;
            }

            $result = $this->runner->run($config, $phpFile);
            if ($result->hasNotChanged()) {
                continue;
            }

            $this->symfonyStyle->section($path);
            $diff = $this->colorConsoleDiff->format($result->diff());
            $this->symfonyStyle->text($diff);
            if ($dryRun) {
                $this->symfonyStyle->block($path, 'DRY-RUN', 'fg=black;bg=yellow', ' ', true);
                continue;
            }

            $this->filesystem->write($path, $result->updatedContent());
            $this->symfonyStyle->block($path, 'FORMATTED', 'fg=black;bg=green', ' ', true);
        }
    }

    private function phormatConfig(string $directory): PhormatConfig
    {
        // if both phormat.php and phormat.dist.php are missing, create phormat.dist.php from phormat.dist.php
        $configPath = $directory . DIRECTORY_SEPARATOR . 'phormat.php';

        if ($this->filesystem->missing($configPath)) {
            $configPath = $directory . DIRECTORY_SEPARATOR . 'phormat.dist.php';

            if ($this->filesystem->missing($configPath)) {
                $this->filesystem->write(
                    $configPath,
                    $this->filesystem->read(\dirname(__DIR__) . '/phormat.dist.php'),
                );
            }
        }

        $config = require $configPath;

        if (! $config instanceof PhormatConfig) {
            throw new InvalidArgumentException(\sprintf(
                'Config file "%s" must return an instance of %s',
                $configPath,
                PhormatConfig::class,
            ));
        }

        return $config;
    }

    //    private array $tasks = [
    //        'determineWorkingDirectory',
    //        'locatePhormatConfig',
    //        'determinePhpFilesThatNeedFormatting',
    //        'formatPhpFiles',
    //        'displayDiff',
    //        'applyFormatting',
    //    ];
    /**
     * @param Closure(): void $task
     *
     * @throws EventDispatcherExceptionInterface
     */
    private function task(string $title, Closure $task): void
    {
        try {
            print $title . PHP_EOL;
            $this->eventDispatcher->dispatch($this->container->call($task));
        } catch (Throwable $throwable) {
            $this->eventDispatcher->dispatch($throwable);
        }
    }

    private function title(): string
    {
        return \sprintf(
            'Phormat <info>(%s)</info> - PHP Code Formatter %s',
            $this->version(),
            '<error>#BlackLivesMatter</error>',
        );
    }

    private function version(): string
    {
        return InstalledVersions::getPrettyVersion(self::GHOSTWRITER_PHORMAT) ?? throw new ShouldNotHappenException();
    }
}
