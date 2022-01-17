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
$pidPath = $argv[3];

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

try {
    // Save PID - Windows
    if (Console::isWindows()) {
        if (file_exists($pidPath)) {
            die('Process already exists');
        }
        $pid = getmypid();

        $parentPids = shell_exec('wmic process get processid,parentprocessid|find "' . $pid . '"');
        $parentPids = preg_replace('/ +/m', ',', str_replace("\r\n", "", $parentPids)) . $pid;
        
        $pidFile = fopen($pidPath, "w");
        if (!$pidFile) {
            throw new \Exception("Unable to open file");
        }
        fwrite($pidFile, $parentPids);
        fclose($pidFile);
    }

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