<?php

namespace ConcreteWebsocket\Websocket;

use Log;

abstract class Console {
    public static function isWindows() {
        return str_contains(strtolower(php_uname('a')), 'windows');
    }

    public static function getPhpExecutable() {
        if (strlen(PHP_BINARY) > 0) return PHP_BINARY;
        return self::isWindows() ? trim(exec('where php')) : substr(trim(exec('whereis php')), 5);
    }

    public static function isProcessRunning($pid) {
        if (self::isWindows()) {
            $command = sprintf('tasklist | find " %s "', $pid);
        } else {
            $command = sprintf('ps -A | grep "%s "', $pid);
        }
        return strlen(trim($pid)) > 0 && strlen(trim(exec($command))) > 0;
    }

    public static function isProcessTreeRunning($pids) {
        foreach ($pids as $pid) {
            if (self::isProcessRunning($pid)) return true;
        }
        return false;
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
            $command = sprintf('start cmd /k "' . $phpExecutable . ' "%s" "%s" "%s" "%s"" && exit', ...$values);
            return pclose(popen($command, "r"));
        } else {
            $command = sprintf($phpExecutable . ' "%s" "%s" "%s" "%s"', ...$values);
            return exec($command.' > /dev/null & echo $!');
        }
    }
}
