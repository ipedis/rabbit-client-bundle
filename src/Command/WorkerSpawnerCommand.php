<?php


namespace Ipedis\Bundle\Rabbit\Command;


use Ipedis\Bundle\Rabbit\Service\Container\WorkerContainer;
use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);


        if( ! $this->workerContainer->has($input->getArgument('name')) ) {
            $io->error(sprintf("No service tagged with this name : %s", $input->getArgument('name')));
            return -1;
        }

        /** @var ProcessInterface $worker */
        $worker = $this->workerContainer->get($input->getArgument('name'));

        if (!($worker instanceof ProcessInterface)) {
            $io->error(sprintf("Registred service does not implement ProcessInterface", $input->getArgument('name')));
            return -1;
        }

        $io->success(sprintf("Registred service does not implement ProcessInterface", $input->getArgument('name')));

        $worker
            ->execute();

        return 0;
    }
}
