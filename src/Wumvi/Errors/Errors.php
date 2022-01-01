<?php
declare(strict_types=1);

namespace Wumvi\Errors;

use Wumvi\Utils\Response;

class Errors
{
    public const HTTP_CODE_5XX_EXCEPTION_HANDLER = 550;
    public const HTTP_CODE_5XX_COMMON_INTERNAL_ERROR = 551;
    public const HTTP_CODE_5XX_ERROR_RESPONSE = 552;

    /**
     * @param array $custom
     * @param bool $isEnvLog
     *
     * @throws
     */
    public static function attachExceptionHandler(
        array $custom = [],
        bool $isEnvLog = true
    ): void {
        set_exception_handler(
            static function (\Throwable $exception) use ($custom, $isEnvLog) {
                $json = json_encode([
                    'msg' => $exception->getMessage(),
                    'url' => $_SERVER['REQUEST_URI'] ?? 'no-uri',
                    'host' => $_SERVER['HTTP_HOST'] ?? 'no-host',
                    'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'https',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                    'port' => $_SERVER['SERVER_PORT'] ?? '0',
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
                http_response_code(self::HTTP_CODE_5XX_EXCEPTION_HANDLER);
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
     * @param bool $isError Ошибка или нет
     * @param string $error Сообщение
     * @param string $hint Подсказка
     * @param int $httpStatus Http status
     *
     * @codeCoverageIgnore
     */
    public static function conditionExit(
        bool $isError,
        string $error,
        string $hint = '',
        int $httpStatus = self::HTTP_CODE_5XX_COMMON_INTERNAL_ERROR
    ): void {
        if ($isError) {
            http_response_code($httpStatus);
            Response::flush(Response::jsonError($error, $hint));
            exit;
        }
    }

    /**
     * @param * $result Data
     * @param int $httpStatus Http status
     */
    public static function conditionErrorResponse(
        $result,
        int $httpStatus = self::HTTP_CODE_5XX_ERROR_RESPONSE
    ): void {
        if ($result instanceof ErrorResponse) {
            http_response_code($httpStatus);
            self::conditionExit(true, $result->name, $result->hint);
        }
    }
}
