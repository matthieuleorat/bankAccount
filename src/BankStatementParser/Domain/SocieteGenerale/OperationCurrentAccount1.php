<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\BankStatementParser\Domain\SocieteGenerale;

use DateTimeImmutable;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class OperationCurrentAccount1
{
    private DateTimeImmutable $valeur;
    private DateTimeImmutable $date;
    private string $details;
    private float $montant;
}
