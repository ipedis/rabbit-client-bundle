<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Connectable;

/**
 * Parent service of the event dispatchers and listeners: it receives the `event` exchange configuration
 * (see Resources/config/services.yaml).
 */
abstract class EventConnectable extends Connectable
{
}
