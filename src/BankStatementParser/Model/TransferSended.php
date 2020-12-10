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

class TransferSended extends AbstractType
{
    /**
    "000001 VIR EUROPEEN EMIS LOGITEL
    POUR: M. DUPONT JEAN
    26 10 SG 00991 CPT 00065498732
    REF: 32165498765432
    MOTIF: ANY REASON
    CHEZ: SOGEFRPP";
     */

    const NAME = 'transfer_received';

    const REF_KEY = "\nREF: ";
    const REF_SUB_PATTERN = "(".self::REF_KEY."(\d+))?";

    const FOR_KEY = "\nPOUR: ";
    const FOR_SUB_PATTERN = "(".self::FOR_KEY."([\s\S]*?))";

    const REASON_KEY = "\nMOTIF: ";
    const REASON_SUB_PATTERN = "(".self::REASON_KEY."(.*))?";

    const TO_KEY = "\nCHEZ: ";
    const TO_SUB_PATTERN = "(".self::TO_KEY."(.*))?";

    const PATTERN = '/^(\d+)\sVIR EUROPEEN EMIS LOGITEL'.self::FOR_SUB_PATTERN.'(\d{2} \d{2})?\sSG\s(\d+)\sCPT\s(\d+)'.self::REF_SUB_PATTERN.''.self::REASON_SUB_PATTERN.''.self::TO_SUB_PATTERN.'/';

    /**
     * @var string
     */
    private $number;
    /**
     * @var string
     */
    private $for;
    /**
     * @var string
     */
    private $date;
    /**
     * @var string
     */
    private $account;
    /**
     * @var string
     */
    private $ref;
    /**
     * @var string|null
     */
    private $reason;
    /**
     * @var string
     */
    private $to;

    private function __construct()
    {}

    public static function create(array $matches) : TypeInterface
    {
        $obj = new self();
        $obj->number = $matches[1];
        $obj->date = $matches[4];
        $obj->account = $matches[6];
        $obj->for = $obj->tryToGuess(self::FOR_KEY, $matches);
        $obj->ref = $obj->tryToGuess(self::REF_KEY, $matches);
        $obj->to = $obj->tryToGuess(self::TO_KEY, $matches);
        $obj->reason = $obj->tryToGuess(self::REASON_KEY, $matches);

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
    public function getFor(): string
    {
        return $this->for;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getAccount(): string
    {
        return $this->account;
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * @return string|null
     */
    public function getReason(): ? string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }
}
