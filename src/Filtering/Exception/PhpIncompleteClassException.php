<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filtering\Exception;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class PhpIncompleteClassException extends \Exception
{
    private object $transaction;
    private string $attribute;

    public function __construct($message, $transaction, $attribute)
    {
        $this->transaction = $transaction;
        $this->attribute = $attribute;
        parent::__construct($message);
    }

    public function getTransaction() : object
    {
        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
