<?php

namespace Concrete\Package\ConcreteWebSocket\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;
use ConcreteWebSocket\WebSocket\Console;
use ConcreteWebSocket\WebSocket\Process;
use ConcreteWebSocket\WebSocket\Manager\ProcessManager;
use ConcreteWebSocket\WebSocket\Manager\SettingsManager;

class WebSocket extends DashboardPageController {
    public function view() {
        $canExec = Console::canExecute();
        $processes = Process::scan();
        $settings = SettingsManager::getAll();

        $errors = [];
        if ($this->get('websocketError')) {
            $errors[] = $this->get('websocketError');
        }
        if (!$canExec) {
            $errorMessage = t("exec, shell_exec and similar functions are disabled, this prevents WebSocket servers from starting.\nContact your server administrator and ask him to change this setting.\nConcrete WebSocket is safe and open-source, we use exec (or similar) only and exclusively to start, shut-down and control WebSocket servers.\nEdit your php.ini file (placed at %s) to enable it, see the FAQs on GitHub to see how to do it.");
            $iniPaths = [function_exists('php_ini_loaded_file') ? php_ini_loaded_file() : ''];
            if ($extraIni = php_ini_scanned_files()) {
                $iniPaths .= ", " . $extraIni;
            }
            $errors[] = sprintf($errorMessage, count($iniPaths) > 0 ? implode(',', $iniPaths) : t('unknown path'));
        }

        $this->set('canExec', $canExec);
        $this->set('errors', $errors);
        $this->set('processes', $processes);
        $this->set('settings', $settings);
        $this->requireAsset('css', 'concrete_websocket_css');
    }

    public function edit($id) {
        $processManager = ProcessManager::getInstance();
        $processArray = $processManager->getById($id);

        try {
            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }
            if (!$this->token->validate('concrete_websocket_process_form_'.$id)) {
                throw new \Exception(t("Invalid token. Please refresh the page and try again."));
            }
            $port = $_POST['port'];
            if (!is_numeric($port) || $port < 1024 || $port > 65535) {
                throw new \Exception(sprintf(t("The port must be a number between %s and %s."), 1024, 65535));
            }

            $processManager->update($id, [
                'port' => $port,
            ]);

            return Redirect::to('/dashboard/websocket');
        } catch (\Throwable $th) {
            $this->set('websocketError', $th->getMessage());
            $this->view();
        }
    }

    public function start($id) {
        $processArray = ProcessManager::getInstance()->getById($id);

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
            $this->set('websocketError', $th->getMessage());
            $this->view();
        }
    }

    public function stop($id) {
        $processArray = ProcessManager::getInstance()->getById($id);

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
            $this->set('websocketError', $th->getMessage());
            $this->view();
        }
    }

    public function restart($id) {
        $processArray = ProcessManager::getInstance()->getById($id);

        try {
            if (!$processArray) {
                throw new \Exception(t("Process not found."));
            }

            $process = Process::create($processArray['class']);
            $process->stop();
            $process->start();

            return Redirect::to('/dashboard/websocket');
        } catch (\Throwable $th) {
            $this->set('websocketError', $th->getMessage());
            $this->view();
        }
    }

    public function settings() {
        $args = $this->request->request->all();

        if ($args && $this->token->validate('concrete_websocket_settings_form')) {
            $errors = $this->validateSettings($args);
            $this->error = $errors;

            if (!$errors->has()) {
                SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD, $args[CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD]);
                SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_PHP_PATH, $args[CONCRETEWEBSOCKET_SETTINGS_PHP_PATH]);
            }

            $this->flash('success', t('Settings saved'));
            $this->redirect('/dashboard/websocket');
        }
    }

    public function validateSettings($args) {
        $e = $this->app->make('helper/validation/error');

        if ($args[CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD] == '') {
            $e->add(t('You must specify a REST API password.'));
        }
        if ($args[CONCRETEWEBSOCKET_SETTINGS_PHP_PATH] == '') {
            $e->add(t('You must specify a valid PHP executable path.'));
        }

        return $e;
    }
}
