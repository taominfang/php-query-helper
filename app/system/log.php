<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Log
 *
 * @author mtao60
 */
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class MLog {

    //put your code here
    public static $lever_debug = 4;
    public static $lever_info = 3;
    public static $lever_error = 2;
    public static $print_caller = true;
    public static $log_lever = 4;

    private static function getCaller() {

        if (PHP_VERSION_ID <= 50306) {
            $callTrace = debug_backtrace();
        } else {
            $callTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }


        if (!empty($callTrace[1]) && !empty($callTrace[1]['file']) && !empty($callTrace[1]['line'])) {
            return "MLog from: {$callTrace[1]['file']} ({$callTrace[1]['line']}) \n";
        } else {
            var_export($callTrace[1]);
            return "";
        }
    }

    public static function callerTrace($str = "") {

        if (PHP_VERSION_ID <= 50306) {
            $callTrace = debug_backtrace();
        } else {
            $callTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        $trace = "{$str}\n";

        for ($i1 = 0; !empty($callTrace[$i1]) && !empty($callTrace[$i1]['file']) && !empty($callTrace[$i1]['line']); $i1++) {
            $trace.="{$callTrace[$i1]['file']} ({$callTrace[$i1]['line']}) \n";
        }

        error_log($trace);
    }

    public static function d($str) {

        if (MLog::$log_lever < MLog::$lever_debug) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[debug]' . MLog::getCaller() . $str);
        } else {
            error_log('[debug]' . MLog::getCaller() . $str);
        }
    }

    public static function i($str) {

        if (MLog::$log_lever < MLog::$lever_info) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[info]' . MLog::getCaller() . $str);
        } else {
            error_log('[info]' . MLog::getCaller() . $str);
        }
    }

    public static function e($str) {

        if (MLog::$log_lever < MLog::$lever_error) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[error]' . MLog::getCaller() . $str);
        } else {
            error_log('[error]' . MLog::getCaller() . $str);
        }
    }

    public static function dExport($var, $messageHeader = "", $messageTailer = "") {

        if (MLog::$log_lever < MLog::$lever_debug) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[debug]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        } else {
            error_log('[debug]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        }
    }

    public static function iExport($var, $messageHeader = "", $messageTailer = "") {

        if (MLog::$log_lever < MLog::$lever_info) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[info]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        } else {
            error_log('[info]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        }
    }

    public static function eExport($var, $messageHeader = "", $messageTailer = "") {

        if (MLog::$log_lever < MLog::$lever_error) {
            return;
        }
        if (MLog::$print_caller) {
            error_log('[error]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        } else {
            error_log('[error]' . MLog::getCaller() . $messageHeader . var_export($var, true) . $messageTailer);
        }
    }

}

?>
