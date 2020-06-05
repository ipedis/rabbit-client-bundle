<?php

namespace Ipedis\Bundle\Rabbit\Service\Manager;


use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;

class WorkflowManager extends OrderConnectable
{
    use \Ipedis\Rabbit\Workflow\Manager;
}
