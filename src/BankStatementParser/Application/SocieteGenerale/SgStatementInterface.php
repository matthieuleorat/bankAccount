<?php
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\BankStatementParser\Application\SocieteGenerale;

use \DateTimeImmutable;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
interface SgStatementInterface
{
    public function getStartingDate(): DateTimeImmutable;

    public function getEndingDate(): DateTimeImmutable;

    public function getPreviousBalance(): float;

    public function getNewBalance(): float;

    public function getAccountNumber(): string;

    public function getCredit() : float;

    public function getDebit() : float;

    public function getOperations(): array;
}
