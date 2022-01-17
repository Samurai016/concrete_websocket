<?php

namespace ConcreteWebsocket\Websocket;

use ConcreteWebsocket\Websocket\Manager\ProcessManager;
use InvalidArgumentException;

class Process {
    protected $id;
    protected $port;
    protected $pid;
    protected $class;
    protected $status = 'off';
    protected $errors;

    function __construct($values = null) {
        if ($values) {
            $this->id = $values['id'];
            $this->port = $values['port'];
            $this->pid = $values['pid'];
            $this->class = $values['class'];
            $this->status = $values['status'];
            $this->errors = $values['errors'];
        }
        $this->checkRunning();
    }

    public static function create($class) {
        if (!file_exists($class)) {
            throw new InvalidArgumentException(sprintf(t("Class %s does not exists."), $class));
        }
        $processArray = ProcessManager::getByClass($class);

        // Check if process already exists
        if ($processArray) {
            $process = new self($processArray);
        } else { // Either create it
            $process = new self();

            // Search valid port
            $validPort = null;
            while (is_null($validPort)) {
                $port = self::generateRandomPort();
                if (!is_null(ProcessManager::getByPort($port))) {
                    $validPort = $port;
                }
            }

            $process->id = ProcessManager::add([
                'class' => $class,
                'port' => $validPort,
            ]);
            $process->port = $validPort;
            $process->class = $class;
        }

        return $process;
    }

    public function start() {
        $pidPath = join(DIRECTORY_SEPARATOR, [__DIR__, "pid_" . $this->id . ".txt"]);
        $output = Console::startProcess(PATH_START, $this->class, $this->port, $pidPath);

        if (Console::isWindows()) {
            sleep(1);
    
            if (!file_exists($pidPath)) {
                throw new \Exception(t("Error while starting process, log gile for pid not exists"));
            }
    
            $pidFile = fopen($pidPath, "r") or die(t("Unable to open file!"));
            $pid = fread($pidFile, filesize($pidPath));
            fclose($pidFile);
            unlink($pidPath);
        } else {
            $pid = ctype_digit($output) ? $output : '';
        }

        if ($pid) {
            ProcessManager::update($this->id, ['pid' => $pid, 'status' => 'on']);
        } else {
            throw new \Exception(t("Error while starting process, pid not readable"));
        }
    }

    public function stop() {
        $pids = ProcessManager::getPidsById($this->id);
        foreach ($pids as $pid) {
            Console::killProcess($pid);
        }
        $this->stopOnDb();
    }

    public function checkRunning() {
        $isRunning = Console::isProcessTreeRunning($this->getPids());
        if (!$isRunning && $this->status == 'on') {
            $this->status = 'off';
            $this->pid = null;
            $this->stopOnDb();
        }
    }

    public function getID() {
        return $this->id;
    }

    public function getPort() {
        return $this->port;
    }

    public function getPids() {
        return explode(',', trim($this->pid));
    }

    public function getClass() {
        return $this->class;
    }

    public function getClassName() {
        return '\\Application\\Websocket\\' . basename($this->class, '.php');
    }

    public function getName() {
        return basename($this->class, '.php');
    }

    public function getStatus() {
        return $this->status;
    }

    public function setErrors($errors) {
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function stopOnDb() {
        ProcessManager::update($this->id, ['pid' => null, 'status' => 'off']);
    }

    private static function generateRandomPort() {
        return rand(29170, 29998);
    }
}
