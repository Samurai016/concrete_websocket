<?php

namespace ConcreteWebsocket\Websocket\Routes;

class ConcreteAuthenticationRoutes {
    public static function registerRoutes($router) {
        $router->get(CONCRETEWEBSOCKET_CONCRETE_AUTH_ENDPOINT, [static::class, 'handshake']);
    }

    public static function handshake() {
        $user = new \User();

        if ($user->isRegistered()) {
            echo $user->getUserID();
        } else {
            http_response_code(401);
            echo t('Not logged');
        }

        die();
    }
}
