<?php

namespace Bdf\Prime\Shell;

use Bdf\Prime\ServiceLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for display the prime shell
 */
class PrimeShellCommand extends Command
{
    protected static $defaultName = 'prime:shell';

    /**
     * @var ServiceLocator
     */
    private $locator;

    /**
     * CacheCommand constructor.
     *
     * @param ServiceLocator $locator
     */
    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;

        parent::__construct(static::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Run the prime interactive shell');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory = new ShellFactory($this->locator);
        $factory->create()->run();

        return 0;
    }
}
