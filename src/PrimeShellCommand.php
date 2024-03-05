<?php

namespace Bdf\Prime\Shell;

use Bdf\Collection\Stream\Streams;
use Bdf\Collection\Util\Functor\Transformer\Getter;
use Bdf\Prime\ServiceLocator;
use Bdf\Util\File\ClassFileLocator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for display the prime shell
 *
 * @api
 */
#[AsCommand('prime:shell', 'Run the prime interactive shell')]
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
        $this
            ->setDescription('Run the prime interactive shell')
            ->addOption('--preload', '-p', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Preload classes from given paths', [])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Streams::wrap($input->getOption('preload'))
            ->flatMap(function (string $path) { return new ClassFileLocator(realpath($path)); })
            ->map(new Getter('getClass'))
            ->forEach(function (string $className) { class_exists($className, true); })
        ;

        $factory = new ShellFactory($this->locator);
        $factory->create()->run();

        return 0;
    }
}
