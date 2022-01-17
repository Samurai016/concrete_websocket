<?php

namespace ConcreteWebsocket\Websocket\Middleware;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Http\CloseResponseTrait;
use Psr\Http\Message\RequestInterface;

/**
 * A middleware to ensure JavaScript clients connecting are logged in Concrete5.
 * This protects your application from behind used by unauthorized users.
 */
class ConcreteCheck implements HttpServerInterface {
    use CloseResponseTrait;

    protected $_component;

    public function __construct(MessageComponentInterface $component) {
        $this->_component = $component;
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $header = $request->hasHeader('Cookie') ? (string)$request->getHeader('Cookie')[0] : '';

        // If authentication cookies are missing, I refuse the connection
        if (!$header) {
            return $this->close($conn, 403);
        } else {
            // If I am not authenticated I refuse the connection
            $ch = curl_init();

            // I get the domain
            // TODO: Controlla per spoof
            preg_match_all('/(https?:\/\/.+):\d+/', $request->getUri(), $matches, PREG_SET_ORDER, 0);
            if (count($matches)<=0)
                return $this->close($conn, 400);
            $url = $matches[0][1].'/index.php/'.CONCRETE_CHECK_ENDPOINT;

            // I make the request
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: ".$header));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $body = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode!=200)
                return $this->close($conn, 401);
        }

        return $this->_component->onOpen($conn, $request);
    }

    function onMessage(ConnectionInterface $from, $msg) {
        return $this->_component->onMessage($from, $msg);
    }

    function onClose(ConnectionInterface $conn) {
        return $this->_component->onClose($conn);
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        return $this->_component->onError($conn, $e);
    }
}
