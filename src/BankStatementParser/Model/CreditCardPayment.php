<?php declare(strict_types=1);

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

    private function __construct() {}

    public static function create(array $matches) : TypeInterface
    {
        list (, $cardId, $date, $merchant) = $matches;
        $obj = new self();
        $obj->cardId = $cardId;
        $obj->date = $date;
        $obj->merchant = $merchant;

        return $obj;
    }

    public static function createFormOperation(Operation $operation) : ? TypeInterface
    {
        $obj = parent::createFormOperation($operation);

        if ($obj instanceof TypeInterface) {
            $year = $operation->getDate()->format('Y');
            $obj->date = (\DateTimeImmutable::createFromFormat('d/m/Y', $obj->date.'/'.$year));
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
