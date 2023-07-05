<?php

namespace ConcreteWebSocket\WebSocket;

use ConcreteWebSocket\WebSocket\Manager\SettingsManager;

abstract class Console {
    public static function isWindows() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public static function getPhpExecutable() {
        return self::isWindows() ? trim(static::execute('where php')) : PHP_BINDIR . '/php';
    }

    public static function isProcessRunning($pid) {
        if (self::isWindows()) {
            $command = sprintf('tasklist | find " %s "', $pid);
        } else {
            $command = sprintf('ps -A | grep "%s "', $pid);
        }
        return strlen(trim($pid)) > 0 && str_contains(static::execute($command), $pid);
    }

    public static function isProcessTreeRunning($pids) {
        foreach ($pids as $pid) {
            if (self::isProcessRunning($pid)) return true;
        }
        return false;
    }

    public static function isPortFree($port) {
        if (self::isWindows()) {
            $command = sprintf('netstat -ano | find "%s"', $port);
        } else {
            $command = sprintf('netstat -tulpn | grep "%s"', $port);
        }
        return !str_contains(static::execute($command), $port);
    }

    public static function killProcess($pid) {
        if (self::isWindows()) {
            $command = sprintf("wmic process where processid=%s delete", $pid);
        } else {
            $command = sprintf('kill -9 %s', $pid);
        }
        return static::execute($command);
    }

    public static function startProcess(...$values) {
        $phpExecutable = SettingsManager::get(CONCRETEWEBSOCKET_SETTINGS_PHP_PATH);
        if (!$phpExecutable) {
            throw new \Exception(t('PHP executable path not set.'));
        }

        if (self::isWindows()) {
            $command = sprintf($phpExecutable . ' "%s" "%s" "%s"', ...$values);
            $command = sprintf('start /b wmic process call create "%s" | find "ProcessId"', $command);
            return static::execute($command);
        } else {
            $command = sprintf($phpExecutable . ' "%s" "%s" "%s"', ...$values);
            return static::execute($command . ' > /dev/null & echo $!');
        }
    }

    public static function canExecute() {
        return function_exists('exec') ||
            function_exists('shell_exec') ||
            function_exists('system') ||
            function_exists('proc_open') ||
            function_exists('passthru') ||
            function_exists('popen');
    }

    public static function execute($command) {
        if (function_exists('exec')) {
            return exec($command);
        } else if (function_exists('shell_exec')) {
            return trim(shell_exec($command));
        } else if (function_exists('system')) {
            ob_start();
            $result = system($command);
            ob_end_clean();
            return $result;
        } else if (function_exists('proc_open')) {
            $process = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
            $output = '';
            while (!feof($pipes[1])) {
                $output .= fread($pipes[1], 1024);
            }
            proc_close($process);
            return trim($output);
        } else if (function_exists('passthru')) {
            ob_start();
            passthru($command);
            $output = trim(ob_get_contents());
            ob_end_clean();
            return $output;
        } else if (function_exists('popen')) {
            $fp = popen($command, 'r');
            $output = '';
            while (!feof($fp)) {
                $output .= fread($fp, 1024);
            }
            pclose($fp);
            return trim($output);
        } else {
            throw new \Exception(t('No function available to execute commands'));
        }
    }
}
