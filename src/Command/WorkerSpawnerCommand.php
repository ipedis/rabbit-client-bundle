<?php


namespace Ipedis\Bundle\Rabbit\Command;


use Ipedis\Bundle\Rabbit\Service\Container\WorkerContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerSpawnerCommand extends Command
{
    protected static $defaultName = 'ip:worker:spawner';
    /**
     * @var WorkerContainer
     */
    private $workerContainer;

    public function __construct(WorkerContainer $workerContainer, string $name = null)
    {
        parent::__construct($name);
        $this->workerContainer = $workerContainer;
    }

    public function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'name of the worker to spawn');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->workerContainer);
        return 0;
    }
}