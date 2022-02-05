<?php

namespace Concrete\Package\ConcreteWebsocket\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\File\StorageLocation\Configuration\LocalConfiguration;
use Concrete\Core\Support\Facade\Application;
use ConcreteWebsocket\Websocket\Process;
use ConcreteWebsocket\Websocket\Constants;
use ConcreteWebsocket\Websocket\ErrorLogger;
use ConcreteWebsocket\Websocket\Manager\ProcessManager;

class Websocket extends DashboardPageController {
    public function view() {
        $processes = $this->scan();
        $errors = ErrorLogger::getAll();
        foreach ($errors as $class => $error) {
            for ($i = 0; $i < count($processes); $i++) {
                if ($processes[$i]->getClass() == $class) {
                    $processes[$i]->setErrors($error);
                    break;
                }
            }
        }

        $this->set('execAvailable', function_exists('exec'));
        $this->set('processes', $processes);
        $this->requireAsset('css', 'concrete_websocket_css');
    }

    public function start($id) {
        $processArray = ProcessManager::getById($id);

        try {
            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }
            if ($processArray['status'] == 'on') {
                throw new \Exception(t("Unable to start process, it is already started."));
            }

            $process = Process::create($processArray['class']);
            $process->start();

            return Redirect::to('/dashboard/websocket');
        } catch (\Throwable $th) {
            $this->set('websocketError', $th);
            $this->view();
        }
    }

    public function stop($id) {
        $processArray = ProcessManager::getById($id);

        try {
            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }
            if ($processArray['status'] == 'off') {
                throw new \Exception(t("Unable to stop process, it is already stopped."));
            }

            $process = Process::create($processArray['class']);
            $process->stop();

            return Redirect::to('/dashboard/websocket');
        } catch (\Throwable $th) {
            $this->set('websocketError', $th);
            $this->view();
        }
    }

    public function delete_error($id, $date) {
        $processArray = ProcessManager::getById($id);

        try {
            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }

            $process = new Process($processArray);
            ErrorLogger::remove($process->getClass(), $date);

            die(json_encode(['success' => true]));
        } catch (\Throwable $th) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => $th->getMessage()]));
        }
    }

    private function scan() {
        // Scan files
        $app = Application::getFacadeApplication();
        $factory = $app->make(StorageLocationFactory::class);
        $folder = $factory->fetchByName(Constants::$STORAGE_NAME);
        if (!$folder) {
            $configuration = new LocalConfiguration();
            $configuration->setRootPath(Constants::$PATH_SCAN);
            $folder = $factory->create($configuration, Constants::$STORAGE_NAME);
        }
        $filesystem = $folder->getFileSystemObject();

        $files = $filesystem->listContents('.', false);
        $processes = array();
        $processesIds = array();
        foreach ($files as $file) {
            if ($file['type'] == 'file' && $file['extension'] == 'php') {
                $filePath = realpath(join(DIRECTORY_SEPARATOR, [Constants::$PATH_SCAN, $file['path']]));
                $process = Process::create($filePath);
                $processes[] = $process;
                $processesIds[] = $process->getClass();
            }
        }

        // Check for deleted files
        $oldProcessesIds = ProcessManager::getAllColumn('class');
        if (count($oldProcessesIds) > count($processesIds)) {
            $toRemove = array_diff($oldProcessesIds, $processesIds);
            foreach ($toRemove as $class) {
                ProcessManager::deleteByClass($class);
            }
        }

        return $processes;
    }
}
