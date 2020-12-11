<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BankStatementParser;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
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
