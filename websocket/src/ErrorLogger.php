<?php

namespace ConcreteWebsocket\Websocket;

abstract class ErrorLogger {
    public static function add(string $error, string $class) {
        if (!file_exists(CONCRETE_WS_PATH_ERROR)) return;
        $file = fopen(CONCRETE_WS_PATH_ERROR, "a");

        $lines = explode("\n", $error);
        $date = date('Y-m-d H:i:s');
        foreach ($lines as $key => $line) {
            $lines[$key] = sprintf('[%s][%s] %s', $date, $class, $line);
        }
        $log = implode("\n", $lines) . "\n";

        fwrite($file, $log);
        fclose($file);
    }

    public static function remove(string $class, string $date) {
        if (!file_exists(CONCRETE_WS_PATH_ERROR)) return;
        $file = fopen(CONCRETE_WS_PATH_ERROR, "r");
        if (filesize(CONCRETE_WS_PATH_ERROR)<=0) return;
        $errors = fread($file, filesize(CONCRETE_WS_PATH_ERROR));
        fclose($file);

        $class = str_replace('\\', '\\\\', $class);
        $pattern = sprintf('/\[%s\]\[%s\].+\n?/m', $date, str_replace('/', '\/', $class));
        $errors = preg_replace($pattern, "", $errors);

        $file = fopen(CONCRETE_WS_PATH_ERROR, "w");
        fwrite($file, $errors);
        fclose($file);
    }

    public static function getAll() {
        if (!file_exists(CONCRETE_WS_PATH_ERROR)) return [];
        $file = fopen(CONCRETE_WS_PATH_ERROR, "r");
        if (filesize(CONCRETE_WS_PATH_ERROR)<=0) return [];
        $content = fread($file, filesize(CONCRETE_WS_PATH_ERROR));
        fclose($file);

        $errors = array();
        $lines = explode("\n", trim($content));
        foreach ($lines as $line) {
            preg_match_all('/\[(.+)\]\[(.+)\] (.+)/m', $line, $matches, PREG_SET_ORDER, 0);
            if (count($matches) > 0) {
                $date = $matches[0][1];
                $class = $matches[0][2];
                $error = $matches[0][3];
                if (!$errors[$class]) $errors[$class] = array();
                if (!$errors[$class][$date]) $errors[$class][$date] = '';
                $errors[$class][$date] .= $error . "\r\n";
            }
        }
        return $errors;
    }
}
