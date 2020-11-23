<?php declare(strict_types=1);

namespace App\BankStatementParser;

class AmoutFormatter
{
    public static function formatFloat($amout) : float
    {
        $amout = str_replace(' *', '', $amout);
        $amout = str_replace('.', '', $amout);
        $amout = str_replace(',', '.', $amout);
        $amout = str_replace(' ', '', $amout);

        return (float) $amout;
    }
}
