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
      protocol_version: "v1"
      service_name: "admin"
      
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

Get Started: Publish and Subscribe.
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
            - { name: "ipedis_rabbit.worker", key: "binding" }

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

Get Started: Mananger and Worker
==

Create service Manager as following:
    
    use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;
    use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
    use PhpAmqpLib\Message\AMQPMessage;
    
    class Manager extends OrderConnectable implements ProcessInterface
    {
        use \Ipedis\Rabbit\Order\Manager;
    
        protected $taskIsFinish;
    
        public function execute()
        {
            $this->taskIsFinish = false;
            $this->connect();
            $anoQueue = $this->bindCallbackToAnonymousQueue([$this,"callback"]);
    
            $this->publishTask(Worker::getQueueName(),
                [
                    "name" => "task"
                ],
                $anoQueue,
                'task'
            );
    
            while (!$this->taskIsFinish) {
                $this->channel->wait();
            }
        }
    
        /**
         * @description will be executed as soon as worker will send findback on anonymous queue.
         * @param AMQPMessage $message
         */
        public function callback(AMQPMessage $message) {
            $params = json_decode($message->getBody(),true);
            switch ($params['status']) {
                case "PROGRESS":
                    $this->onProgress($message);
                    break;
                case "SUCCESS":
                    $this->onSuccess($message);
                    break;
                case "ERROR":
                    $this->onError($message);
                    break;
            }
    
        }
    
        private function onProgress(AMQPMessage $message)
        {
            // [...]
        }
    
        private function onSuccess(AMQPMessage $message)
        {
            // [...]
            $this->taskIsFinish = true;
        }
    
        private function onError(AMQPMessage $message)
        {
            // [...]
            $this->taskIsFinish = true;
        }
        public function __destruct()
        {
            $this->disconnect();
        }
    }


and config as following

    App\Service\Manager:
        parent: Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable
        autoconfigure: false
        autowire: true
        tags:
            - { name: "ipedis_rabbit.worker", key: "manager" }


Create Service Worker as following:

    use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;
    use Ipedis\Bundle\Rabbit\Service\Contract\ProcessInterface;
    use PhpAmqpLib\Message\AMQPMessage;
    
    class Worker extends OrderConnectable implements ProcessInterface
    {
        use \Ipedis\Rabbit\Order\Worker;
    
        public static function getQueueName(): string
        {
            return OrderChannel::fromString('v1.admin.publication.generate');
        }
    
        protected function getProcessing(): \Closure
        {
            return function (AMQPMessage $req) {
    
                $this->notifyTo($req, ['status' => 'PROGRESS', 'step' => 1]);
    
                return ["foo" => "bar"];
            };
        }
    }

and config as following:

    App\Service\Worker:
        parent: Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable
        autoconfigure: false
        autowire: true
        tags:
            - { name: "ipedis_rabbit.worker", key: "worker" }
