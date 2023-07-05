<?php

namespace ConcreteWebSocket\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Class WebSocketServer  
 *   
 * This abstract class represents a WebSocket base server.  
 * Extends this class when you want to create a WebSocket server for Concrete WebSocket.  
 */
abstract class WebSocketServer implements MessageComponentInterface {
    protected $clients;

    /**
     * WebSocketServer constructor.
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    
    /**
     * Get the middlewares for the WebSocket server.
     * The middlewares are executed in the order they are defined (LIFO queue).  
     * If a middleware returns `false`, the next middlewares will not be executed.  
     * Every middleware **must extends** the `ConcreteWebSocket\WebSocket\Middleware\Middleware` class.  
     *   
     * @return ConcreteWebSocket\WebSocket\Middleware\Middleware[] An array of middlewares.
     */
    public static function getMiddlewares() {
        return [];
    }

    /**
     * Called when a new connection is opened.
     * 
     * @param ConnectionInterface $conn The connection that was opened.
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    /**
     * Called when a message is received from a connection.
     * 
     * @param ConnectionInterface $from The connection from which the message was received.
     * @param mixed $msg The received message.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
    }

    /**
     * Called when a connection is closed.
     * 
     * @param ConnectionInterface $conn The connection that was closed.
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    /**
     * Called when an error occurs on a connection.
     * 
     * @param ConnectionInterface $conn The connection where the error occurred.
     * @param \Exception $e The exception representing the error.
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
