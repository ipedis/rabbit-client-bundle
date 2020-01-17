RabbitMQ - Publispeak client symfony Bundle
--

Installation
==

Update `composer.json` and add a repository:

    "repositories": [
        {
            "type": "vcs",
            "url": "bitbucket:ipedis/rabbit-client.git"
        },
        {
            "type": "vcs",
            "url": "bitbucket:ipedis/rabbit-client-bundle.git"
        }
    ]
    
    
Require the library:

    "require": {
        "ipedis/rabbit-client-bundle": "^1.0.0"
    }

----

Configuration
==

on `config/packages` folder, create yaml configuration like following:

    ipedis_rabbit:
      connection:
        host: "localhost"
        port: 5672
        user: "guest"
        password: "guest"
    
      order:
        exchange: "publispeak_orders"
        type: "topic"
    
      event:
        exchange: "publispeak_events"
        type: "topic"

*all configurations have default value so there are all optional*

---

on `config/bundles.php` add `RabbitBundle` as bellow:

    Ipedis\Bundle\Rabbit\RabbitBundle::class => ['all' => true]

---

Get Started
==

**Create event listener worker**

Create service like following: 

    use Closure;
    use Ipedis\Bundle\Rabbit\Service\Connectable\Connectable;
    use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
    use Ipedis\Rabbit\Event\EventListener;
    use PhpAmqpLib\Message\AMQPMessage;
    
    class Binding extends Connectable implements ProcessInterface
    {
        use EventListener;
        
        protected function getProcessing(): Closure
        {
            return function (AMQPMessage $message) {
                $data = json_decode($message->getBody(), true);
                // [...]
            };
        }
    
        protected function getBindingKey(): string
        {
            return 'publication.*';
        }
    }

----

Create service configuration as following:

    App\Service\Binding:
        parent: Ipedis\Bundle\Rabbit\Service\Connectable\EventConnectable
        autoconfigure: false
        autowire: true
        tags:
            - { name: "ipedis_rabbit.worker", key: "binding"}

*on tag, `binding` key will be used to identify worker from cli*

    php bin/console ip:worker:spawner binding

----

**Event Dispatcher**

require service `Ipedis\Bundle\Rabbit\Service\Dispatcher\EventDispatcher` and dispatch any event as :

    public function index(EventDispatcher $dispatcher)
    {
        $dispatcher->dispatchEvent('publication.was-exported', [
            'publication' => ['sid' => 1234]
        ]);
    }

it will use `connection` and `event` configuration from bundle configuration.
