<?php

namespace App\Exceptions;

use Exception;

class BatchOverlapException extends Exception
{
    public static function datesOverlap(string $startDate, string $endDate): self
    {
        return new self(
            "As datas do lote ({$startDate} a {$endDate}) se sobrepõem a outro lote existente para este evento."
        );
    }

    public static function invalidDateRange(string $message = 'Intervalo de datas inválido'): self
    {
        return new self($message);
    }
}
