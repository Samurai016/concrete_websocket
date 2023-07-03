<?php

namespace ConcreteWebsocket\Websocket;

use ConcreteWebsocket\Websocket\Manager\ProcessManager;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\File\StorageLocation\Configuration\LocalConfiguration;
use Concrete\Core\Support\Facade\Application;
use InvalidArgumentException;
use JsonSerializable;

class Process implements JsonSerializable {
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
        $processManager = ProcessManager::getInstance();
        $processArray = $processManager->getByClass($class);

        // Check if process already exists
        if ($processArray) {
            $process = new self($processArray);
        } else { // Either create it
            $process = new self();

            $process->id = $processManager->add([
                'class' => $class,
            ]);
            $process->class = $class;
        }

        return $process;
    }

    public function start() {
        $this->checkPort();
        $output = Console::startProcess(CONCRETEWEBSOCKET_PATH_START, $this->class, $this->port);

        if (Console::isWindows()) {
            preg_match('/ProcessId = (\d+)/m', $output, $matches);
            $pid = $matches ? $matches[1] : null;
        } else {
            $pid = ctype_digit($output) ? $output : '';
        }

        if ($pid) {
            ProcessManager::getInstance()->update($this->id, ['pid' => $pid, 'status' => 'on']);
        } else {
            throw new \Exception(t("Error while starting process, pid not readable"));
        }
    }

    public function stop() {
        $process = ProcessManager::getInstance()->getById($this->id);
        $pids = explode(',', trim($process['pid']));
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

    public function checkPort() {
        if (!$this->port || !Console::isPortFree($this->port)) {
            // Search valid port
            $validPort = null;
            while (is_null($validPort)) {
                $port = self::generateRandomPort();
                if (Console::isPortFree($port)) {
                    $validPort = $port;
                }
            }

            $this->port = $validPort;
            if ($this->id) {
                ProcessManager::getInstance()->update($this->id, ['port' => $validPort]);
            }
        }
    }

    public static function scan() {
        // Scan files
        $app = Application::getFacadeApplication();
        $factory = $app->make(StorageLocationFactory::class);
        $folder = $factory->fetchByName(CONCRETEWEBSOCKET_STORAGE_NAME);
        if (!$folder) {
            $configuration = new LocalConfiguration();
            $configuration->setRootPath(CONCRETEWEBSOCKET_PATH_SCAN);
            $folder = $factory->create($configuration, CONCRETEWEBSOCKET_STORAGE_NAME);
        }
        $filesystem = $folder->getFileSystemObject();

        $files = $filesystem->listContents('.', false);
        $processes = array();
        $processesIds = array();
        foreach ($files as $file) {
            if ($file['type'] == 'file' && $file['extension'] == 'php') {
                $filePath = realpath(join(DIRECTORY_SEPARATOR, [CONCRETEWEBSOCKET_PATH_SCAN, $file['path']]));
                $process = self::create($filePath);
                $processes[] = $process;
                $processesIds[] = $process->getClass();
            }
        }

        // Check for deleted files
        $processManager = ProcessManager::getInstance();
        $oldProcessesIds = array_column($processManager->getAll(), 'class');
        if (count($oldProcessesIds) > count($processesIds)) {
            $toRemove = array_diff($oldProcessesIds, $processesIds);
            foreach ($toRemove as $class) {
                $processManager->deleteByClass($class);
            }
        }

        return $processes;
    }

    // Getters
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

    // Utils
    private function stopOnDb() {
        ProcessManager::getInstance()->update($this->id, ['pid' => null, 'status' => 'off']);
    }

    private static function generateRandomPort() {
        return rand(29170, 29998);
    }

    // JSON
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'port' => $this->port,
            'pid' => $this->pid,
            'class' => $this->class,
            'status' => $this->status,
            'errors' => $this->errors,
        ];
    }
}
