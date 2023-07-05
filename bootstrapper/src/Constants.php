<?php

namespace ConcreteWebSocket\WebSocket;

class Constants {
    public static function registerConstants() {
        // Paths
        // I generate paths by relative paths because I don't have access to the Concrete CMS constants
        defined('CONCRETEWEBSOCKET_BASE_PATH') or define('CONCRETEWEBSOCKET_BASE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__,'..','..', '..', '..'])));
        defined('CONCRETEWEBSOCKET_PACKAGE_PATH') or define('CONCRETEWEBSOCKET_PACKAGE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__, "..", ".."])));
        
        defined('CONCRETEWEBSOCKET_PATH_DATABASE') or define('CONCRETEWEBSOCKET_PATH_DATABASE',  realpath(join(DIRECTORY_SEPARATOR, [CONCRETEWEBSOCKET_BASE_PATH, "application", "config", "database.php"])));
        defined('CONCRETEWEBSOCKET_PATH_START') or define('CONCRETEWEBSOCKET_PATH_START', realpath(join(DIRECTORY_SEPARATOR, [CONCRETEWEBSOCKET_PACKAGE_PATH, 'bootstrapper', 'server.php'])));
        defined('CONCRETEWEBSOCKET_PATH_SCAN') or define('CONCRETEWEBSOCKET_PATH_SCAN',  join(DIRECTORY_SEPARATOR, [CONCRETEWEBSOCKET_BASE_PATH, "application", "websocket"]));
        
        // Database tables
        defined('CONCRETEWEBSOCKET_TABLE_PROCESSES') or define('CONCRETEWEBSOCKET_TABLE_PROCESSES', 'ConcreteWebSocketProcesses');
        defined('CONCRETEWEBSOCKET_TABLE_SETTINGS') or define('CONCRETEWEBSOCKET_TABLE_SETTINGS', 'ConcreteWebSocketSettings');
        
        // Settings
        defined('CONCRETEWEBSOCKET_SETTINGS_WEBHOOK') or define('CONCRETEWEBSOCKET_SETTINGS_WEBHOOK', "cw_authentication_webhook");
        defined('CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD') or define('CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD', "cw_api_password");
        defined('CONCRETEWEBSOCKET_SETTINGS_PHP_PATH') or define('CONCRETEWEBSOCKET_SETTINGS_PHP_PATH', "cw_php_path");

        // Password Middleware
        defined('CONCRETEWEBSOCKET_PASSWORD_HEADER') or define('CONCRETEWEBSOCKET_PASSWORD_HEADER', "X-WebSocket-Password");
        defined('CONCRETEWEBSOCKET_PASSWORD_PARAM') or define('CONCRETEWEBSOCKET_PASSWORD_PARAM', "pwd");

        // Miscellaneous
        defined('CONCRETEWEBSOCKET_STORAGE_NAME') or define('CONCRETEWEBSOCKET_STORAGE_NAME', 'concrete_websocket');
        defined('CONCRETEWEBSOCKET_LOG_CHANNEL') or define('CONCRETEWEBSOCKET_LOG_CHANNEL', 'concrete_websocket');
        defined('CONCRETEWEBSOCKET_CONCRETE_AUTH_ENDPOINT') or define('CONCRETEWEBSOCKET_CONCRETE_AUTH_ENDPOINT', "/concrete_websocket/handshake");
        defined('CONCRETEWEBSOCKET_API_BASE') or define('CONCRETEWEBSOCKET_API_BASE', "/concrete_websocket/api");
    }
}
