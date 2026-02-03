<?php

namespace App\Exceptions;

use Exception;

/**
 * Exceção para erros de integração com sistemas externos
 */
class IntegracaoException extends Exception
{
    public function __construct(
        string $message = 'Erro na integração com sistema externo',
        int $code = 503
    ) {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_type' => 'integration_error',
        ], $this->code);
    }
}
