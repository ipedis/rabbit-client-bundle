<?php


namespace Ipedis\Bundle\Rabbit\Service\Dispatcher;


use Ipedis\Bundle\Rabbit\Service\Connectable\EventConnectable;

class EventDispatcher extends EventConnectable
{
    use \Ipedis\Rabbit\Event\EventDispatcher;
}
