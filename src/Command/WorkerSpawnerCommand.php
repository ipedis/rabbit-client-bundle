<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Command;

use Ipedis\Bundle\Rabbit\Service\Container\WorkerContainer;
use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ip:worker:spawner', description: 'Spawn a worker by its name')]
class WorkerSpawnerCommand extends Command
{
    public function __construct(private readonly WorkerContainer $workerContainer, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'name of the worker to spawn');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        /** @var string $workerName */
        $workerName = $input->getArgument('name');

        if (! $this->workerContainer->has($workerName)) {
            $symfonyStyle->error(sprintf('No service tagged with this name : "%s"', $workerName));
            return -1;
        }

        $worker = $this->workerContainer->get($workerName);

        if (!($worker instanceof ProcessInterface)) {
            $symfonyStyle->error(sprintf('Registred service "%s" does not implement "ProcessInterface"', $workerName));
            return -1;
        }

        $symfonyStyle->success(sprintf('Worker "%s" is up and ready to start processing.', $workerName));

        $worker
            ->execute();

        return self::SUCCESS;
    }
}
