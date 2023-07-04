<?php

namespace ConcreteWebsocket\Websocket\Middleware;

/**
 * Class Middleware
 * 
 * This class represents a middleware for WebSocket servers.
 */
class Middleware {
    protected $class;
    protected $params;

    /**
     * Middleware constructor.
     * 
     * @param string $class The class associated with the middleware.
     * @param mixed ...$params Additional parameters for the middleware. They will be passed to the constructor of the class.
     */
    public function __construct($class, ...$params) {
        $this->class = $class;
        $this->params = $params;
    }

    /**
     * Get the class associated with the middleware.
     * 
     * @return string The class associated with the middleware.
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Get the additional parameters for the middleware.
     * 
     * @return array Additional parameters for the middleware.
     */
    public function getParams() {
        return $this->params;
    }
}
