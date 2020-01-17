<?php


namespace Ipedis\Bundle\Rabbit\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerSpawnerCommand extends Command
{
    protected static $defaultName = 'ip:worker:spawner';
    protected $workers = [];

    public function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'name of the worker to spawn');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        dump($this->workers);
    }

    protected function addWorker($worker)
    {
        $this->workers[] = $worker;
    }
}