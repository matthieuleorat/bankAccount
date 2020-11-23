<?php declare(strict_types=1);

namespace App\BankStatementParser\Model;

use App\BankStatementParser\AmoutFormatter;

class HomeLoan extends AbstractType
{
    const NAME = 'home_loan';

    const MONEY_AMOUT = "((\d{1,3}\.)?\d{1,3},\d{2})";

    const REF_LOAN_MATURITY = "ECHEANCE PRET N°";
    const REF_LOAN_MATURITY_SUB_PATTERN = "(".self::REF_LOAN_MATURITY."(\d+))";

    const DEPRECIATED_CAPITAL_KEY = "\nCAPITAL AMORTI : ";
    const DEPRECIATED_CAPITAL_SUB_PATTERN = "(".self::DEPRECIATED_CAPITAL_KEY.self::MONEY_AMOUT.")";

    const INTEREST_KEY = "\nINTERETS : ";
    const INTEREST_SUB_PATTERN = "(".self::INTEREST_KEY.self::MONEY_AMOUT.")";

    const INSURANCE_KEY = "\nASSURANCE : ";
    const INSURANCE_SUB_PATTERN = "(".self::INSURANCE_KEY.self::MONEY_AMOUT.")";

    const REMAINING_CAPITAL_KEY = "\nCAPITAL RESTANT : ";
    const REMAINING_CAPITAL_SUB_PATTERN = "(".self::REMAINING_CAPITAL_KEY."((\d{1,3}\s)?\d{1,3},\d{2}))?";

    const EXPECTED_END_DATE_KEY = "\nDATE PREVISIONNELLE DE FIN : ";
    const EXPECTED_END_DATE_SUB_PATTERN = "(".self::EXPECTED_END_DATE_KEY."(\d{1,2}\/\d{1,2}\/\d{4}))";

    const PATTERN = '/^'.self::REF_LOAN_MATURITY_SUB_PATTERN.self::DEPRECIATED_CAPITAL_SUB_PATTERN.self::INTEREST_SUB_PATTERN.self::INSURANCE_SUB_PATTERN.self::REMAINING_CAPITAL_SUB_PATTERN.self::EXPECTED_END_DATE_SUB_PATTERN.'/';

    /**
     * @var string
     */
    private $loanNumber;
    /**
     * @var float
     */
    private $depreciatedCapital;
    /**
     * @var float
     */
    private $interest;
    /**
     * @var float
     */
    private $insurance;
    /**
     * @var float
     */
    private $remainingCapital;
    /**
     * @var string
     */
    private $expectedEndDate;

    private function __construct()
    {}

    public static function create(array $matches) : TypeInterface
    {
        $obj = new self();
        $obj->loanNumber = $matches[2];
        $obj->depreciatedCapital = AmoutFormatter::formatFloat($obj->tryToGuess(self::DEPRECIATED_CAPITAL_KEY, $matches));
        $obj->interest = AmoutFormatter::formatFloat($obj->tryToGuess(self::INTEREST_KEY, $matches));
        $obj->insurance = AmoutFormatter::formatFloat($obj->tryToGuess(self::INSURANCE_KEY, $matches));
        $obj->remainingCapital = AmoutFormatter::formatFloat($obj->tryToGuess(self::REMAINING_CAPITAL_KEY, $matches));
        $obj->expectedEndDate = $obj->tryToGuess(self::EXPECTED_END_DATE_KEY, $matches);

        return $obj;
    }

    /**
     * @return string
     */
    public function getLoanNumber(): string
    {
        return $this->loanNumber;
    }

    /**
     * @return float
     */
    public function getDepreciatedCapital(): float
    {
        return $this->depreciatedCapital;
    }

    /**
     * @return float
     */
    public function getInterest(): float
    {
        return $this->interest;
    }

    /**
     * @return float
     */
    public function getInsurance(): float
    {
        return $this->insurance;
    }

    /**
     * @return float
     */
    public function getRemainingCapital(): float
    {
        return $this->remainingCapital;
    }

    /**
     * @return string
     */
    public function getExpectedEndDate(): string
    {
        return $this->expectedEndDate;
    }
}
