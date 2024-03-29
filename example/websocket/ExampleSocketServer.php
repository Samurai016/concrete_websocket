<?php

namespace Application\WebSocket;

use ConcreteWebSocket\WebSocket\WebSocketServer;
use ConcreteWebSocket\WebSocket\Middleware\ConcreteAuthentication;
use ConcreteWebSocket\WebSocket\Middleware\Middleware;
use Ratchet\ConnectionInterface;
use Ratchet\Http\OriginCheck;

class ExampleSocketServer extends WebSocketServer {
    public static function getMiddlewares() {
        return [
            new Middleware(OriginCheck::class, ['localhost']),
            new Middleware(ConcreteAuthentication::class),
        ];
    }

    public function onOpen(ConnectionInterface $conn) {
        parent::onOpen($conn);
        echo 'New connection';
        $conn->send('Welcome');
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo 'New message';
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        parent::onClose($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        parent::onError($conn, $e);
    }
}
