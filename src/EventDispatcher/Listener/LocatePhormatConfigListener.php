<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Listener;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Ghostwriter\Phormat\EventDispatcher\Event\LocatePhormatConfig;
use Ghostwriter\Phormat\PhormatConfig;
use Ghostwriter\Phormat\Workspace;
use InvalidArgumentException;
use Symfony\Component\Console\Style\SymfonyStyle;

use const DIRECTORY_SEPARATOR;

final readonly class LocatePhormatConfigListener
{
    public function __construct(
        private SymfonyStyle $symfonyStyle,
        private ContainerInterface $container,
        private FilesystemInterface $filesystem
    ) {
    }

    public function __invoke(LocatePhormatConfig $locatePhormatConfig): void
    {
        $workspace = $locatePhormatConfig->workspace();
        $configPath = $this->configPath($workspace);
        $this->symfonyStyle->writeln('Using config file: ' . $configPath);
        $config = require $configPath;
        if (! $config instanceof PhormatConfig) {
            $this->symfonyStyle->error(
                \sprintf('Config file "%s" must return an instance of %s', $configPath, PhormatConfig::class)
            );
            throw new InvalidArgumentException(\sprintf(
                'Config file "%s" must return an instance of %s',
                $configPath,
                PhormatConfig::class
            ));
        }

        die(\var_dump([$config]));
        $this->container->set(PhormatConfig::class, $config);
    }

    public function configPath(Workspace $workspace): string
    {
        $configPath = $workspace->path('phormat.php');
        if ($this->filesystem->missing($configPath)) {
            $configPath = $workspace->path('phormat.dist.php');
            if ($this->filesystem->missing($configPath)) {
                $this->filesystem->write(
                    $configPath,
                    $this->filesystem->read(\dirname(__DIR__) . DIRECTORY_SEPARATOR . 'phormat.dist.php')
                );
            }
        }

        return $configPath;
    }
}
