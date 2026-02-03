<?php

namespace App\Exceptions;

use Exception;

/**
 * Exceção para erros de regra de negócio
 * Retorna 422 Unprocessable Entity
 */
class BusinessException extends Exception
{
    public function __construct(
        string $message = 'Erro de validação de negócio',
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    /**
     * Renderiza a exceção como resposta JSON
     */
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_type' => 'business_validation',
        ], $this->code);
    }
}
