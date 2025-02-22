<?php

namespace App\Exceptions;

use Exception;

class AlephApiException extends Exception
{
    public function __construct(
        string $message = "",
        private readonly int $apiErrorCode = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $apiErrorCode, $previous);
    }

    public function getApiErrorCode(): int
    {
        return $this->apiErrorCode;
    }

    public static function unauthorized(): self
    {
        return new self(
            'El usuario no existe o no se encuentra habilitado para hacer uso del API.',
            401
        );
    }

    public static function apiKeyNotFound(): self
    {
        return new self(
            'API key no encontrada.',
            403
        );
    }

    public static function internalServerError(): self
    {
        return new self(
            'Algo salió mal. Por favor, inténtelo de nuevo más tarde o contáctese con soporte@alephmanager.com',
            500
        );
    }

}
