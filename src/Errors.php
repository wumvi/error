<?php
declare(strict_types=1);

namespace Wumvi\Errors;

use Wumvi\Utils\Response;

class Errors
{
    /**
     * @param array $custom
     * @param bool $isEnvLog
     *
     * @codeCoverageIgnore
     *
     * @throws
     */
    public static function attachExceptionHandler(array $custom = [], bool $isEnvLog = true): void
    {
        set_exception_handler(
            static function (\Throwable $exception) use ($custom, $isEnvLog) {
                $json = json_encode([
                    'msg' => $exception->getMessage(),
                    'url' => $_SERVER['REQUEST_URI'],
                    'host' => $_SERVER['HTTP_HOST'],
                    'protocol' => $_SERVER['SERVER_PROTOCOL'],
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'port' => $_SERVER['SERVER_PORT'],
                    'trace' => $exception->getTrace(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                    'time' => $_SERVER['REQUEST_TIME'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'no-user-agent',
                    'hostname' => gethostname(),
                    'image_version' => $_ENV['IMAGE_VERSION'] ?? 'no-image-version',
                    'container_id' => $_ENV['CONTAINER_ID'] ?? 'no-container-id',
                    'env' => $isEnvLog ? $_ENV : [],
                    'custom' => $custom,
                ], JSON_THROW_ON_ERROR);
                error_log($json);
                http_response_code(550);
                if (error_reporting() === E_ALL) {
                    throw $exception;
                }
                Response::flush(Response::jsonError($exception->getMessage(), 'uncaught-exception'));
                exit;
            }
        );
    }

    /**
     * Завершает программу со ШТАТНОЙ ошибкой.
     *
     * @param bool $condition Ошибка
     *                          или нет
     * @param string $error Сообщение
     * @param string $hint Подсказка
     *
     * @codeCoverageIgnore
     */
    public static function conditionExit(bool $condition, string $error, string $hint = ''): void
    {
        if ($condition) {
            Response::flush(Response::jsonError($error, $hint));
            exit;
        }
    }

    public static function conditionErrorResponse($result): void
    {
        if ($result instanceof ErrorResponse) {
            self::conditionExit(true, $result->getName(), $result->getHint());
        }
    }
}
