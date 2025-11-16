<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public static function processingFailed(string $message = 'Falha ao processar pagamento'): self
    {
        return new self($message);
    }

    public static function invalidPaymentData(string $message = 'Dados de pagamento inválidos'): self
    {
        return new self($message);
    }

    public static function mercadoPagoError(string $message): self
    {
        return new self("Erro do Mercado Pago: {$message}");
    }

    public static function callbackValidationFailed(string $message = 'Falha na validação do callback de pagamento'): self
    {
        return new self($message);
    }

    public static function packageNotFound(string $packageNumber): self
    {
        return new self("Pacote {$packageNumber} não encontrado");
    }
}
