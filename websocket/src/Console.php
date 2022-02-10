<?php

namespace ConcreteWebsocket\Websocket;

abstract class Console {
    public static function isWindows() {
        return str_contains(strtolower(php_uname('a')), 'windows');
    }

    public static function getPhpExecutable() {
        return self::isWindows() ? trim(exec('where php')) : substr(trim(exec('whereis php')), 5);
    }

    public static function isProcessRunning($pid) {
        if (self::isWindows()) {
            $command = sprintf('tasklist | find " %s "', $pid);
        } else {
            $command = sprintf('ps -A | grep "%s "', $pid);
        }
        return strlen(trim($pid)) > 0 && str_contains(exec($command), $pid);
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
        return !str_contains(exec($command), $port);
    }

    public static function killProcess($pid) {
        if (self::isWindows()) {
            $command = sprintf("wmic process where processid=%s delete", $pid);
        } else {
            $command = sprintf('kill -9 %s', $pid);
        }
        return exec($command);
    }

    public static function startProcess(...$values) {
        $phpExecutable = self::getPhpExecutable();
        if (self::isWindows()) {
            $command = sprintf($phpExecutable.' "%s" "%s" "%s"', ...$values);
            $command = sprintf('start /b wmic process call create "%s" | find "ProcessId"', $command);
            return exec($command);
        } else {
            $command = sprintf($phpExecutable . ' "%s" "%s" "%s"', ...$values);
            return exec($command.' > /dev/null & echo $!');
        }
    }
}
