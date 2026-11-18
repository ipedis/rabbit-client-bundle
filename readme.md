# Rabbit Client Bundle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ipedis/rabbit-client-bundle.svg)](https://packagist.org/packages/ipedis/rabbit-client-bundle)
[![License](https://img.shields.io/packagist/l/ipedis/rabbit-client-bundle.svg)](https://packagist.org/packages/ipedis/rabbit-client-bundle)

Symfony integration of [`ipedis/rabbit-client`](https://github.com/ipedis/rabbit-client): RabbitMQ events, orders and workflows, configured from `ipedis_rabbit` and run with one console command.

The bundle provides:

- an event dispatcher, an order manager and a workflow manager, ready to inject;
- abstract parent services (`EventConnectable`, `OrderConnectable`) that give your listeners and workers the connection settings;
- `ip:worker:spawner <name>`, which starts any listener or worker you tagged;
- validation of every payload against a JSON schema per channel.

## Installation

```bash
composer require ipedis/rabbit-client-bundle
```

Requires PHP 8.3+, Symfony 7.4 and the `amqp` PHP extension (2.0.7: PHP 8.2+ and Symfony 7.4; 2.1.0 and 2.1.1: PHP 8.4 and Symfony 8). Register the bundle in `config/bundles.php` (Symfony Flex does it for you):

```php
Ipedis\Bundle\Rabbit\RabbitBundle::class => ['all' => true],
```

## Configuration

`config/packages/ipedis_rabbit.yaml`. Only `service_name` is required; the other values are the defaults.

```yaml
ipedis_rabbit:
  protocol_version: "v1"
  service_name: "admin"          # your service, the second part of the channel names

  connection:
    host: "localhost"
    port: 5672
    user: "guest"
    password: "guest"

  order:
    exchange: "publispeak_orders"
    type: "topic"
    env: "dev"                   # prefix of the order queues: several environments can share one RabbitMQ

  event:
    exchange: "publispeak_events"
    type: "topic"

  validation:
    enabled: true
    disable_on_dev_mode: false   # skip validation when kernel.environment is "dev"
    schema_base_path: "%kernel.project_dir%/vendor/ipedis/rabbit-client-bundle/Resources/schema"
```

Use environment variables for the connection in production, e.g. `password: '%env(RABBITMQ_PASSWORD)%'`.

## Dispatch an event

Inject `Ipedis\Bundle\Rabbit\Service\Dispatcher\EventDispatcher`:

```php
use Ipedis\Bundle\Rabbit\Service\Dispatcher\EventDispatcher;
use Ipedis\Rabbit\MessagePayload\EventMessagePayload;

public function __construct(private EventDispatcher $dispatcher) {}

public function onExport(int $sid): void
{
    $this->dispatcher->dispatch(EventMessagePayload::build('v1.admin.publication.was-exported', [
        'publication' => ['sid' => $sid],
    ]));
}
```

It uses the `connection` and `event` settings. When RabbitMQ is unreachable, `dispatch()` throws (`RabbitClientConnectException`, `RabbitClientPublishException`): dispatching from a Symfony Messenger handler gives you retries for free.

## Listen to events

```php
use Closure;
use Ipedis\Bundle\Rabbit\Service\Connectable\EventConnectable;
use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
use Ipedis\Rabbit\Event\EventListener;
use Ipedis\Rabbit\MessagePayload\EventMessagePayload;

class PublicationListener extends EventConnectable implements ProcessInterface
{
    use EventListener;

    protected function getBindingKey(): array
    {
        return ['v1.admin.publication.was-exported'];
    }

    protected function getHandledMessages(): iterable
    {
        yield 'v1.admin.publication.was-exported' => ['method' => 'onExported'];
    }

    public function onExported(EventMessagePayload $payload): void
    {
        // ...
    }

    protected function makeMessageHandler(): Closure
    {
        return function (EventMessagePayload $payload): void {
        };
    }

    protected function makeExceptionHandler(): Closure
    {
        return function (\Exception $exception, ?EventMessagePayload $payload): void {
        };
    }
}
```

Register it with the `EventConnectable` parent and a worker key:

```yaml
services:
  App\Listener\PublicationListener:
    parent: Ipedis\Bundle\Rabbit\Service\Connectable\EventConnectable
    autoconfigure: false
    autowire: true
    tags:
      - { name: "ipedis_rabbit.worker", key: "publication" }
```

To inject your own services, add them after the four arguments of the parent constructor (`$connectionConfig`, `$exchangeConfig`, `$channelFactory`, `$messagePayloadValidator`).

Run it (under supervisor or systemd in production):

```bash
php bin/console ip:worker:spawner publication
```

## Orders

A worker consumes one order channel:

```php
use AMQPEnvelope;
use Closure;
use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;
use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
use Ipedis\Rabbit\MessagePayload\OrderMessagePayload;
use Ipedis\Rabbit\Order\Worker;

class GenerateWorker extends OrderConnectable implements ProcessInterface
{
    use Worker;

    protected function getQueueName(): string
    {
        return 'v1.admin.publication.generate';
    }

    protected function makeMessageHandler(): Closure
    {
        return function (AMQPEnvelope $message, OrderMessagePayload $order): array {
            return ['done' => true]; // sent back with the success reply
        };
    }

    protected function makeExceptionHandler(): Closure
    {
        return fn (\Exception $exception, ?OrderMessagePayload $order): array => [];
    }
}
```

```yaml
  App\Worker\GenerateWorker:
    parent: Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable
    autoconfigure: false
    autowire: true
    tags:
      - { name: "ipedis_rabbit.worker", key: "generate" }
```

To send orders and wait for the replies, inject `Ipedis\Bundle\Rabbit\Service\Manager\OrderManager`, or `Ipedis\Bundle\Rabbit\Service\Manager\WorkflowManager` to run a workflow (groups of orders run in sequence, with progress). See the [rabbit-client readme](https://github.com/ipedis/rabbit-client#orders-manager-and-worker) for their API.

## Payload validation

Before a message is published, its data is validated against `<schema_base_path>/<version>/<service>/<aggregate>/<action>/schema.json`, for example `v1/admin/publication/was-exported/schema.json`. A channel without a schema is refused (`MessagePayloadInvalidSchemaException`), so point `schema_base_path` to your own schema folder, or set `enabled: false`.

## Upgrading to 2.2

The recovery fallback of rabbit-client is removed (see its [upgrade notes](https://github.com/ipedis/rabbit-client#upgrading-from-21)):

- `event.recovery_endpoint` and `event.http_signature` are deprecated and ignored: remove them from your configuration (and the environment variables they used). They will be refused in 3.0.
- `EventConnectable` no longer has `getRecoveryEventStoreEndpoint()`, `getSecretKey()`, `getSignatureKey()`, `getHashingAlgorithm()` and `getHeadersList()`.
- `service_name` is now required, and the `connection`, `order`, `event` and `validation` blocks can be left out (their defaults apply).

## Local development

Requires [Docker](https://www.docker.com/).

```bash
make install   # build the container and install the dependencies
make qa        # rector + pint + phpstan + tests
```

## License

Apache License 2.0, see [LICENSE](LICENSE). Maintained by [Ipedis](https://www.ipedis.com) for its Publispeak platform, provided as-is.
