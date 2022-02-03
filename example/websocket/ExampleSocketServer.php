<?php

namespace Application\Websocket;

use ConcreteWebsocket\Websocket\WebSocketServer;
use ConcreteWebsocket\Websocket\Middleware\ConcreteCheck;
use ConcreteWebsocket\Websocket\Middleware\Middleware;
use Ratchet\ConnectionInterface;
use Ratchet\Http\OriginCheck;

class ExampleSocketServer extends WebSocketServer {
    public static function getMiddlewares() {
        return [
            new Middleware(OriginCheck::class, 'localhost'),
            new Middleware(ConcreteCheck::class),
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
