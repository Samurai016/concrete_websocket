<?php

namespace ConcreteWebSocket\WebSocket\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonHandler {
    protected $db;
    protected $u;
    protected $handlers = [];

    public function __construct() {
        $this->db = \Database::connection();
        $this->u = new \User();
    }

    public function registerHandler($method, $handler) {
        $this->handlers[$method] = $handler;
    }

    public function get($handler) {
        $this->registerHandler('get', $handler);
    }
    public function post($handler) {
        $this->registerHandler('post', $handler);
    }
    public function put($handler) {
        $this->registerHandler('put', $handler);
    }
    public function delete($handler) {
        $this->registerHandler('delete', $handler);
    }

    public function handle() {
        $result = [];
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        try {
            if (!isset($this->handlers[$method])) {
                $result['error'] = 'Method not allowed';
                http_response_code(405);
            } else {
                $result['data'] = $this->handlers[$method]($this->db, $this->u);
            }
        } catch (\Throwable $th) {
            if (http_response_code() == 200) {
                http_response_code(500);
            }
            $result['errors'] = [ $th->getMessage() ];
        }

        $result['error'] = isset($result['error']);
        return new JsonResponse($result, http_response_code());
    }
}
