<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\SocketHandler;
use Monolog\Formatter\LogstashFormatter;

class LogstashLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('logstash');
        
        $handler = new SocketHandler("tcp://{$config['host']}:{$config['port']}");
        $handler->setFormatter(new LogstashFormatter(config('app.name')));
        
        $logger->pushHandler($handler);

        return $logger;
    }
}
