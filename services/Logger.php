<?php

function _log_dir() {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    return $dir;
}

function _log_emit($level, $tag, $message, $data = null) {
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] [$level] [$tag] $message";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $json = $json ?: var_export($data, true);
            if (strlen($json) > 2000) $json = substr($json, 0, 2000) . '...[truncated]';
            $line .= " | data=" . $json;
        } else {
            $str = (string)$data;
            if (strlen($str) > 2000) $str = substr($str, 0, 2000) . '...[truncated]';
            $line .= " | data=" . $str;
        }
    }

    $file = _log_dir() . '/ai.log';
    @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $line . PHP_EOL);
    } else {
        @error_log($line);
    }
}

function log_info($tag, $message, $data = null)  { _log_emit('INFO',  $tag, $message, $data); }
function log_warn($tag, $message, $data = null)  { _log_emit('WARN',  $tag, $message, $data); }
function log_error($tag, $message, $data = null) { _log_emit('ERROR', $tag, $message, $data); }
function log_debug($tag, $message, $data = null) { _log_emit('DEBUG', $tag, $message, $data); }

function read_recent_logs($limit = 200) {
    $file = _log_dir() . '/ai.log';
    if (!file_exists($file)) return [];
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return [];
    $lines = array_slice($lines, -$limit);
    return array_reverse($lines);
}

function clear_logs() {
    $file = _log_dir() . '/ai.log';
    return @file_put_contents($file, '') !== false;
}
