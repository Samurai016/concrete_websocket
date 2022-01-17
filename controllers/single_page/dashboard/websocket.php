<?php

namespace Concrete\Package\ConcreteWebsocket\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;
use ConcreteWebsocket\Websocket\Process;
use ConcreteWebsocket\Websocket\ErrorLogger;
use ConcreteWebsocket\Websocket\Manager\ProcessManager;

class Websocket extends DashboardPageController {
    public function view() {
        $processes = $this->scan();
        $errors = ErrorLogger::getAll();
        foreach ($errors as $class => $error) {
            for ($i=0; $i < count($processes); $i++) { 
                if ($processes[$i]->getClass()==$class) {
                    $processes[$i]->setErrors($error);
                    break;
                }
            }
        }

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

            return Redirect::to('/dashboard/websocket');
        } catch (\Throwable $th) {
            $this->set('websocketError', $th);
            $this->view();
        }
    }

    private function scan() {
        // Scan files
        $files = PATH_SCAN ? scandir(PATH_SCAN) : [];
        $processes = array();
        $processesIds = array();
        foreach ($files as $file) {
            $filePath = realpath(join(DIRECTORY_SEPARATOR, [PATH_SCAN, $file]));
            if (pathinfo($filePath, PATHINFO_EXTENSION) == 'php') {
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
