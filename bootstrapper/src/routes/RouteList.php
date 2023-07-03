<?php

namespace ConcreteWebsocket\Websocket\Routes;

use Concrete\Core\Routing\RouteListInterface;

class RouteList implements RouteListInterface {
    public function loadRoutes($router) {
        ConcreteAuthenticationRoutes::registerRoutes($router);
        ApiRoutes::registerRoutes($router);
    }
}
