<?php

define('BASE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__,'..','..', '..'])));
define('PACKAGE_PATH', realpath(join(DIRECTORY_SEPARATOR, [__DIR__, ".."])));
define('PATH_SCAN',  realpath(join(DIRECTORY_SEPARATOR, [BASE_PATH, "application", "websocket"])));
define('PATH_ERROR', realpath(join(DIRECTORY_SEPARATOR, [PACKAGE_PATH, 'error.log'])));
define('PATH_START', realpath(join(DIRECTORY_SEPARATOR, [PACKAGE_PATH, 'websocket', 'server.php'])));
define('TABLE_PROCESSES', 'ConcreteWebsocketProcesses');
define('TABLE_SETTINGS', 'ConcreteWebsocketSettings');
define('CONCRETE_CHECK_ENDPOINT', '/concrete_websocket/handshake');