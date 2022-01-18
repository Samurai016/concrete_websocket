<?php

namespace ConcreteWebsocket\Websocket\Manager;
use Database;

class SettingsManager {
    /**
    * @var \Concrete\Core\Database\Connection\Connection $db
    */
    protected static $db;

    public static function getAll() {
        static::checkDb();
        return static::$db->fetchAll(sprintf('SELECT * FROM %s', CONCRETE_WS_TABLE_SETTINGS));
    }

    public static function get(string $field) {
        static::checkDb();
        return static::$db->fetchAssoc(sprintf('SELECT value FROM %s WHERE field=?', CONCRETE_WS_TABLE_SETTINGS), [$field]);
    }

    public static function set(string $field, string $value) {
        static::checkDb();
        if (static::get($field)) {
            return static::$db->update(CONCRETE_WS_TABLE_SETTINGS, ['value'=>$value], ['field'=>$field]);
        } else {
            static::$db->insert(CONCRETE_WS_TABLE_SETTINGS, ['field'=>$field, 'value'=>$value]);
            return static::$db->lastInsertId();
        }
    }

    public static function delete(string $field) {
        static::checkDb();
        return static::$db->delete(CONCRETE_WS_TABLE_SETTINGS, ['field'=>$field]);
    }

    private static function checkDb() {
        if (is_null(static::$db)) 
            static::$db = Database::connection();
    }
}
