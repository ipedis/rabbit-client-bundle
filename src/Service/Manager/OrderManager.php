<?php

namespace Ipedis\Bundle\Rabbit\Service\Manager;


use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;

class OrderManager extends OrderConnectable
{
    use \Ipedis\Rabbit\Order\Manager;
}
