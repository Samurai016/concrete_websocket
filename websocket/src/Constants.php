<?php

namespace ConcreteWebsocket\Websocket;

class Constants {
    public static $BASE_PATH;
    public static $PACKAGE_PATH;
    public static $PATH_SCAN;
    public static $PATH_DATABASE;
    public static $PATH_START;
    public static $TABLE_PROCESSES;
    public static $TABLE_SETTINGS;
    public static $STORAGE_NAME;
    public static $LOG_CHANNEL;
    public static $CONCRETE_CHECK_ENDPOINT;

    static function initialize() {
        static::$BASE_PATH = realpath(join(DIRECTORY_SEPARATOR, [__DIR__,'..','..', '..', '..']));
        static::$PACKAGE_PATH = realpath(join(DIRECTORY_SEPARATOR, [__DIR__, "..", ".."]));
        static::$PATH_DATABASE =  realpath(join(DIRECTORY_SEPARATOR, [static::$BASE_PATH, "application", "config", "database.php"]));
        static::$PATH_START = realpath(join(DIRECTORY_SEPARATOR, [static::$PACKAGE_PATH, 'websocket', 'server.php']));
        static::$PATH_SCAN =  join(DIRECTORY_SEPARATOR, [static::$BASE_PATH, "application", "websocket"]);
        static::$TABLE_PROCESSES = 'ConcreteWebsocketProcesses';
        static::$TABLE_SETTINGS = 'ConcreteWebsocketSettings';
        static::$STORAGE_NAME = 'concrete_websocket';
        static::$LOG_CHANNEL = 'concrete_websocket';
        static::$CONCRETE_CHECK_ENDPOINT = "/concrete_websocket/handshake";
    }
}
