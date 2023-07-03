<?php

namespace ConcreteWebsocket\Websocket\Manager;

use Concrete\Core\Support\Facade\Application;

class ProcessManager {
    /**
     * @var \Concrete\Core\Database\Connection\Connection $db
     */
    protected $db;

    /**
     * @var \ConcreteWebsocket\Websocket\Manager\ProcessManager $instance
     */
    protected static $instance = null;

    public function __construct() {
        $app = Application::getFacadeApplication();
        $this->db = $app->make('database')->connection();
    }

    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    // Read
    public function getAll() {
        return $this->db->fetchAll(sprintf('SELECT * FROM %s', CONCRETEWEBSOCKET_TABLE_PROCESSES));
    }

    public function getById(string $id) {
        return $this->db->fetchAssoc(sprintf('SELECT * FROM %s WHERE id=?', CONCRETEWEBSOCKET_TABLE_PROCESSES), [$id]);
    }

    public function getByClass(string $class) {
        return $this->db->fetchAssoc(sprintf('SELECT * FROM %s WHERE class=?', CONCRETEWEBSOCKET_TABLE_PROCESSES), [$class]);
    }

    public function getByPort(string $port) {
        return $this->db->fetchAssoc(sprintf('SELECT * FROM %s WHERE port=?', CONCRETEWEBSOCKET_TABLE_PROCESSES), [$port]);
    }

    // Insert
    public function add($data) {
        $this->db->insert(CONCRETEWEBSOCKET_TABLE_PROCESSES, $data);
        return $this->db->lastInsertId();
    }

    // Update
    public function update(string $id, $data) {
        return $this->db->update(CONCRETEWEBSOCKET_TABLE_PROCESSES, $data, ['id' => $id]);
    }

    // Delete
    public function deleteByID(string $id) {
        return $this->db->delete(CONCRETEWEBSOCKET_TABLE_PROCESSES, ['id' => $id]);
    }

    public function deleteByClass(string $class) {
        return $this->db->delete(CONCRETEWEBSOCKET_TABLE_PROCESSES, ['class' => $class]);
    }
}
