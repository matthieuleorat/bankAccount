<?php declare(strict_types=1);

namespace BankStatementParser\Model;

class TransferReceived extends AbstractType
{
    const REF_SUB_PATTERN = "\nREF: ";
    const ID_SUB_PATTERN = "\nID: ";
    const REASON_SUB_PATTERN = "\nMOTIF: ";
    const NAME = 'transfer_received';
    const PATTERN = '/^VIR(?:EMENT)?\s+RECU\s?(.*)\nDE:\s+([\s\S]*?)('.self::REASON_SUB_PATTERN.'([\s\S]*?))?('.self::REF_SUB_PATTERN.'([\s\S]*?))?('.self::ID_SUB_PATTERN.'([\s\S]*?))?$/';

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string|null
     */
    private $ref;

    /**
     * @var string|null
     */
    private $id;

    private function __construct() {}

    public static function create(array $matches) : TypeInterface
    {
        $obj = new self();
        $obj->number = $matches[1];
        $obj->from = $matches[2];
        $obj->reason = $obj->tryToGuess(self::REASON_SUB_PATTERN, $matches);
        $obj->ref = $obj->tryToGuess(self::REF_SUB_PATTERN, $matches);
        $obj->id = $obj->tryToGuess(self::ID_SUB_PATTERN, $matches);

        return $obj;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return string|null
     */
    public function getRef(): ?string
    {
        return $this->ref;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
