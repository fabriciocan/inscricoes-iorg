<?php

namespace App\Exceptions;

use Exception;

class InvalidPackageStateException extends Exception
{
    public static function cannotModifyConfirmedPackage(): self
    {
        return new self('Não é possível modificar um pacote já confirmado');
    }

    public static function cannotPayCancelledPackage(): self
    {
        return new self('Não é possível pagar um pacote cancelado');
    }

    public static function cannotPayConfirmedPackage(): self
    {
        return new self('Este pacote já foi pago e confirmado');
    }

    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self("Transição de status inválida: de '{$from}' para '{$to}'");
    }

    public static function emptyPackage(): self
    {
        return new self('O pacote não possui inscrições');
    }
}
