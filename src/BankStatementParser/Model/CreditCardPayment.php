<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BankStatementParser\Model;

class CreditCardPayment extends AbstractType
{
    const NAME = 'credit_card_payement';
    const PATTERN = '/^CARTE\s{1}(X\d{4})\s{1}(\d{2}\/\d{2})\s{1}(.*)/s';

    /**
     * @var string
     */
    private $cardId;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var string
     */
    private $merchant;

    private function __construct()
    {
    }

    public static function create(array $matches) : TypeInterface
    {
        list(, $cardId, $date, $merchant) = $matches;
        $obj = new self();
        $obj->cardId = $cardId;
        $obj->date = \DateTimeImmutable::createFromFormat('d/m', $date);
        $obj->merchant = $merchant;

        return $obj;
    }

    public static function createFormOperation(Operation $operation) : ? TypeInterface
    {
        $obj = parent::createFormOperation($operation);

        if ($obj instanceof self) {
            $year = $operation->getDate()->format('Y');
            $date = $obj->date->format('d/m').'/'.$year;
            $obj->date = $date2 = \DateTimeImmutable::createFromFormat('d/m/Y', $date);
        }

        return $obj;
    }

    /**
     * @return string
     */
    public function getCardId(): string
    {
        return $this->cardId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getMerchant(): string
    {
        return $this->merchant;
    }
}
