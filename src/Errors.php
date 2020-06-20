<?php
declare(strict_types=1);

namespace Wumvi\Errors;

use Wumvi\Utils\Response;

class Errors
{
    /**
     * @codeCoverageIgnore
     */
    public static function attachExceptionHandler()
    {
        set_exception_handler(function (\Exception $exception) {
            http_response_code(550);
            if (error_reporting() === E_ALL) {
                throw $exception;
            }
            error_log(json_encode([
                'msg' => $exception->getMessage(),
                'query' => $_SERVER['QUERY_STRING'],
                'method' => $_SERVER['REQUEST_METHOD'],
                'trace' => $exception->getTrace(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]));
            Response::flush(Response::jsonError($exception->getMessage()));
            exit;
        });
    }

    /**
     * Завершает программу со ШТАТНОЙ ошибкой.
     *
     * @param bool $condition Ошибка или нет
     * @param string $error Сообщение
     *
     * @codeCoverageIgnore
     */
    public static function conditionExit(bool $condition, string $error): void
    {
        if ($condition) {
            Response::flush(Response::jsonError($error));
            exit;
        }
    }
}

