<?php

namespace ConcreteWebSocket\WebSocket\Routes\Middleware;

use Concrete\Core\Http\Middleware\MiddlewareInterface;
use Concrete\Core\Http\Middleware\DelegateInterface;
use Symfony\Component\HttpFoundation\Request;
use Concrete\Core\Error\ErrorList\ErrorList;
use ConcreteWebSocket\WebSocket\Manager\SettingsManager;

class PasswordMiddleware implements MiddlewareInterface {
    public function process(Request $request, DelegateInterface $frame) {
        $errors = new ErrorList();
        $password = $request->headers->get(CONCRETEWEBSOCKET_PASSWORD_HEADER);
        $password = $password ? $password : $request->query->get(CONCRETEWEBSOCKET_PASSWORD_PARAM);

        if (!$password) {
            $errors->add(t("You are not authorized to access this resource."));
            return $errors->createResponse(403);
        }

        if ($password !== SettingsManager::get(CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD)) {
            $errors->add(t("You are not authorized to access this resource."));
            return $errors->createResponse(401);
        }
        return $frame->next($request);
    }
}
