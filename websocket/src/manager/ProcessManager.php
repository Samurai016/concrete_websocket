<?php

namespace ConcreteWebsocket\Websocket\Manager;
use Database;

class ProcessManager {
    /**
    * @var \Concrete\Core\Database\Connection\Connection $db
    */
    protected static $db;

    public static function getAll() {
        static::checkDb();
        return static::$db->fetchAll(sprintf('SELECT * FROM %s', TABLE_PROCESSES));
    }

    public static function getAllColumn(string $column) {
        static::checkDb();
        $columnExists = static::$db->fetchColumn(sprintf('SHOW COLUMNS FROM %s WHERE Field = ?', TABLE_PROCESSES), [$column]);
        if ($columnExists) {
            return array_column(static::$db->fetchAll(sprintf('SELECT %s FROM %s', $column, TABLE_PROCESSES)), $column);
        }
        return null;
    }

    public static function getById(string $id) {
        static::checkDb();
        return static::$db->fetchAssoc(sprintf('SELECT * FROM %s WHERE id=?', TABLE_PROCESSES), [$id]);
    }

    public static function getByClass(string $class) {
        static::checkDb();
        return static::$db->fetchAssoc(sprintf('SELECT * FROM %s WHERE class=?', TABLE_PROCESSES), [$class]);
    }

    public static function getByPort(string $port) {
        static::checkDb();
        return static::$db->fetchAssoc(sprintf('SELECT * FROM %s WHERE port=?', TABLE_PROCESSES), [$port]);
    }

    public static function getPidsById(string $id) {
        static::checkDb();
        return explode(',', trim(static::$db->fetchColumn(sprintf('SELECT pid FROM %s WHERE id=?', TABLE_PROCESSES), [$id])));
    }

    public static function add($data) {
        static::checkDb();
        static::$db->insert(TABLE_PROCESSES, $data);
        return static::$db->lastInsertId();
    }

    public static function update(string $id, $data) {
        static::checkDb();
        return static::$db->update(TABLE_PROCESSES, $data, ['id'=>$id]);
    }

    public static function deleteByID(string $id) {
        static::checkDb();
        return static::$db->delete(TABLE_PROCESSES, ['id'=>$id]);
    }

    public static function deleteByClass(string $class) {
        static::checkDb();
        return static::$db->delete(TABLE_PROCESSES, ['class'=>$class]);
    }

    private static function checkDb() {
        if (is_null(static::$db)) 
            static::$db = Database::connection();
    }
}
