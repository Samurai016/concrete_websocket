<?php

namespace ConcreteWebsocket\Websocket;

// Load libraries
require_once join(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use mysqli;

// Load args
$class = $argv[1];
$port = $argv[2];

// Register constants
Constants::initialize();

try {
    // Load handler
    require $class;
    $classes = get_declared_classes();
    $className = pathinfo($class)['filename'];
    for ($i = count($classes) - 1; $i >= 0; $i--) {
        if (preg_match('/Application\\\\Websocket(\\\\.+|)\\\\' . $className . '$/m', $classes[$i])) {
            $classPath = $classes[$i];
            break;
        }
    }

    // Build server
    $wsServer =  new WsServer(
        new $classPath()
    );

    // Build middlwares
    $middlewares = $classPath::getMiddlewares();
    foreach ($middlewares as $middleware) {
        $reflect  = new \ReflectionClass($middleware->getClass());
        $params = array_merge([$wsServer], $middleware->getParams());
        $wsServer = $reflect->newInstanceArgs($params);
    }

    $server = IoServer::factory(
        new HttpServer(
            $wsServer
        ),
        $port
    );

    echo "Server running on localhost:" . $port . "\n";
    $server->run();
} catch (\Throwable $th) {
    // Log errors
    $databaseConfig = require Constants::$PATH_DATABASE;
    if ($databaseConfig) {
        $config = $databaseConfig['connections'][$databaseConfig['default-connection']];
        $db = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

        if ($db) {
            $channel = Constants::$LOG_CHANNEL;
            $level = 400;
            $message = $th->__toString();

            $timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
            if (PHP_VERSION_ID < 70100) { // php7.1+ always has microseconds enabled, so we do not need this hack
                $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), $timezone);
            } else {
                $ts = new \DateTime('now', $timezone);
            }
            $ts->setTimezone($timezone);
            $time = $ts->getTimestamp();

            $stmt = $db->prepare("INSERT INTO Logs (channel, level, message, time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $channel, $level, $message, $time);
            $stmt->execute();
            $db->close();
        }
    }

    throw $th;
}
