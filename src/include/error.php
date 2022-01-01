<?php
const PHP_ERROR_LOG_DIR = '/phplog/';
if (is_dir(PHP_ERROR_LOG_DIR)) {
    ini_set("log_errors", 1);
    ini_set("error_log", PHP_ERROR_LOG_DIR . 'error.log');
}
