<?php
if (!function_exists('safe_die')) {
    function safe_die(...$args)
    {
        $msg = count($args) ? (is_string($args[0]) ? $args[0] : print_r($args[0], true)) : '';
        if (php_sapi_name() === 'cli') {
            die(...$args);
        }
        if (function_exists('log_message')) {
            log_message('error', $msg);
        } else {
            error_log($msg);
        }
        if (!headers_sent()) {
            http_response_code(500);
        }
        exit(1);
    }
}

if (!function_exists('safe_print_r')) {
    function safe_print_r($var, $return = false)
    {
        if (php_sapi_name() === 'cli') {
            return print_r($var, $return);
        }
        $out = print_r($var, true);
        if (function_exists('log_message')) log_message('debug', $out);
        else error_log($out);
        return $return ? $out : null;
    }
}

if (!function_exists('safe_var_dump')) {
    function safe_var_dump(...$vars)
    {
        if (php_sapi_name() === 'cli') {
            foreach ($vars as $v) var_dump($v);
            return;
        }
        ob_start();
        foreach ($vars as $v) var_dump($v);
        $out = ob_get_clean();
        if (function_exists('log_message')) log_message('debug', $out);
        else error_log($out);
    }
}

if (!function_exists('safe_dd')) {
    function safe_dd(...$args)
    {
        if (php_sapi_name() === 'cli') {
            foreach ($args as $a) var_dump($a);
            exit(1);
        }
        foreach ($args as $a) {
            if (function_exists('log_message')) log_message('debug', print_r($a, true));
            else error_log(print_r($a, true));
        }
        exit(1);
    }
}

if (!function_exists('safe_dump')) {
    function safe_dump(...$args)
    {
        if (php_sapi_name() === 'cli') {
            foreach ($args as $a) var_dump($a);
            return;
        }
        foreach ($args as $a) {
            if (function_exists('log_message')) log_message('debug', print_r($a, true));
            else error_log(print_r($a, true));
        }
    }
}
