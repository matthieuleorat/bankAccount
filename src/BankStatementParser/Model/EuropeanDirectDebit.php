<?php  declare(strict_types=1);

namespace App\BankStatementParser\Model;

class EuropeanDirectDebit extends AbstractType
{
    const NAME = 'european_direct_debit';

    const FROM_KEY = "\nDE:\s";
    const FROM_SUB_PATTERN = "".self::FROM_KEY."(.*)";

    const ID_KEY = "\nID:\s";
    const ID_SUB_PATTERN = "".self::ID_KEY."(.*)";

    const REASON_KEY = "\nMOTIF:\s";
    const REASON_SUB_PATTERN = "(?:".self::REASON_KEY."(.*))?";

    const REF_KEY = "\nREF:\s";
    const REF_SUB_PATTERN = "".self::REF_KEY."(.*)";

    const WARRANT_KEY = "\nMANDAT\s";
    const WARRANT_SUB_PATTERN = "".self::WARRANT_KEY."(.*)";

    const PATTERN = "/^PRELEVEMENT EUROPEEN\s(\d*)".self::FROM_SUB_PATTERN."".self::ID_SUB_PATTERN."".self::REASON_SUB_PATTERN."".self::REF_SUB_PATTERN."".self::WARRANT_SUB_PATTERN."$/sU";

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    private $warrant;

    private function __construct() {}

    public static function create(array $matches) : TypeInterface
    {
        list (, $number, $from, $id, $reason, $ref, $warrant) = $matches;
        $obj = new self();
        $obj->number = $number;
        $obj->from = $from;
        $obj->id = $id;
        $obj->reason = $reason;
        $obj->ref = $ref;
        $obj->warrant = $warrant;

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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getWarrant(): string
    {
        return $this->warrant;
    }
}
