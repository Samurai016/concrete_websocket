<?php

namespace ConcreteWebsocket\Websocket;

// Load libraries
require_once join(DIRECTORY_SEPARATOR, [__DIR__, 'vendor', 'autoload.php']);

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

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
    for ($i=count($classes)-1; $i >=0 ; $i--) { 
        if (preg_match('/Application\\\\Websocket(\\\\.+|)\\\\'.$className.'$/m', $classes[$i])) {
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
    ErrorLogger::add($th, $class);
    throw $th;
}