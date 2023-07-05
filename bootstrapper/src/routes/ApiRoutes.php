<?php

namespace ConcreteWebSocket\WebSocket\Routes;

use ConcreteWebSocket\WebSocket\Manager\ProcessManager;
use ConcreteWebSocket\WebSocket\Process;
use ConcreteWebSocket\WebSocket\Routes\Middleware\PasswordMiddleware;
use ConcreteWebSocket\WebSocket\Utils\JsonHandler;

class ApiRoutes {
    public static function registerRoutes($router) {
        $getAll = $router->get(sprintf('%s', CONCRETEWEBSOCKET_API_BASE), [static::class, 'getAll']);
        $getAll->addMiddleware(new PasswordMiddleware());

        $getByID = $router->get(sprintf('%s/{id}', CONCRETEWEBSOCKET_API_BASE), [static::class, 'getByID']);
        $getByID->setRequirements(['id' => '\d+']);
        $getByID->addMiddleware(new PasswordMiddleware());

        $start = $router->get(sprintf('%s/start/{id}', CONCRETEWEBSOCKET_API_BASE), [static::class, 'start']);
        $start->setRequirements(['id' => '\d+']);
        $start->addMiddleware(new PasswordMiddleware());

        $stop = $router->get(sprintf('%s/stop/{id}', CONCRETEWEBSOCKET_API_BASE), [static::class, 'stop']);
        $stop->setRequirements(['id' => '\d+']);
        $stop->addMiddleware(new PasswordMiddleware());

        $restart = $router->get(sprintf('%s/restart/{id}', CONCRETEWEBSOCKET_API_BASE), [static::class, 'restart']);
        $restart->setRequirements(['id' => '\d+']);
        $restart->addMiddleware(new PasswordMiddleware());
    }

    public static function getAll() {
        $handler = new JsonHandler();
        $handler->get(function ($db, $u) {
            return Process::scan();
        });
        return $handler->handle();
    }

    public static function getByID($id) {
        $handler = new JsonHandler();
        $handler->get(function ($db, $u) use ($id) {
            $processArray = ProcessManager::getInstance($db)->getById($id);

            if (!$processArray) {
                http_response_code(400);
                throw new \Exception(t("Process not found."));
            }

            return $processArray;
        });
        return $handler->handle();
    }

    public static function start($id) {
        $handler = new JsonHandler();
        $handler->get(function ($db, $u) use ($id) {
            $processArray = ProcessManager::getInstance($db)->getById($id);

            if (!$processArray) {
                http_response_code(400);
                throw new \Exception(t("Process not found."));
            }
            if ($processArray['status'] == 'on') {
                http_response_code(400);
                throw new \Exception(t("Unable to start process, it is already started."));
            }

            $process = Process::create($processArray['class']);
            $process->start();

            return $process;
        });
        return $handler->handle();
    }

    public static function stop($id) {
        $handler = new JsonHandler();
        $handler->get(function ($db, $u) use ($id) {
            $processArray = ProcessManager::getInstance($db)->getById($id);

            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }
            if ($processArray['status'] == 'off') {
                throw new \Exception(t("Unable to stop process, it is already stopped."));
            }

            $process = Process::create($processArray['class']);
            $process->stop();

            return $process;
        });
        return $handler->handle();
    }

    public static function restart($id) {
        $handler = new JsonHandler();
        $handler->get(function ($db, $u) use ($id) {
            $processArray = ProcessManager::getInstance($db)->getById($id);

            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }

            $process = Process::create($processArray['class']);
            $process->stop();
            $process->start();

            return $process;
        });
        return $handler->handle();
    }
}
