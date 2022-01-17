<?php

namespace ConcreteWebsocket\Websocket\Middleware;

class Middleware {
    protected $class;
    protected $params;

    public function __construct($class, ...$params) {
        $this->class = $class;
        $this->params = $params;
    }

    public function getClass() {
        return $this->class;
    }

    public function getParams() {
        return $this->params;
    }
}
