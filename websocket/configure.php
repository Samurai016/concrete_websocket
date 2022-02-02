<?php defined('C5_EXECUTE') or die("Access Denied.");

define('CONCRETE_WS_BASE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__,'..','..', '..'])));
define('CONCRETE_WS_PACKAGE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__, ".."])));
define('CONCRETE_WS_PATH_SCAN',  realpath(join(DIRECTORY_SEPARATOR, [CONCRETE_WS_BASE_PATH, "application", "websocket"])));
define('CONCRETE_WS_PATH_DATABASE',  realpath(join(DIRECTORY_SEPARATOR, [CONCRETE_WS_BASE_PATH, "application", "config", "database.php"])));
define('CONCRETE_WS_PATH_ERROR', realpath(join(DIRECTORY_SEPARATOR, [CONCRETE_WS_PACKAGE_PATH, 'error.log'])));
define('CONCRETE_WS_PATH_START', realpath(join(DIRECTORY_SEPARATOR, [CONCRETE_WS_PACKAGE_PATH, 'websocket', 'server.php'])));
define('CONCRETE_WS_TABLE_PROCESSES', 'ConcreteWebsocketProcesses');
define('CONCRETE_WS_TABLE_SETTINGS', 'ConcreteWebsocketSettings');
define('CONCRETE_WS_CONCRETE_CHECK_ENDPOINT', '/concrete_websocket/handshake');