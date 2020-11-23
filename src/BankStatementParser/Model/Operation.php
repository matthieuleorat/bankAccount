<?php declare(strict_types=1);

namespace App\BankStatementParser\Model;

use App\BankStatementParser\AmoutFormatter;

class Operation
{
    private const DATE_STARTING_POSITION = 0;
    private const DATE_LENGTH = 11;

    private const VALEUR_STARTING_POSITION = 11;
    private const VALEUR_LENGTH = 11;

    private const MONTANT_PATTERN = '/((\d{1,3}\.)?\d{1,3},\d{2}( \*)?)$/';
    private const MONTANT_STARTING_POSITION = 22;

    /** @var string */
    private $content;

    private $header;

    /** @var \DateTimeImmutable|null */
    private $date = null;

    /** @var \DateTimeImmutable|null */
    private $valeur = null;

    /** @var string|null */
    private $details = null;

    /** @var float|null */
    private $montant = null;

    private $positionMontant = 0;
    /**
     * @var false|int
     */
    private $creditPosition;
    /**
     * @var TypeInterface|null
     */
    private $type;

    private function __construct()
    {
    }

    public static function create(string $header, string $content)
    {
        $obj = new self();
        $obj->content = $content;
        $obj->setHeader($header);
        $obj->setDate();
        $obj->setValeur();
        $obj->setMontant();
        $obj->setDetails();

        return $obj;
    }

    private function setHeader(string $header) : void
    {
        $this->header = $header;
        $this->creditPosition = strpos($this->header, 'Crédit');
    }

    public function isDebit() : bool
    {
        if ($this->positionMontant < $this->creditPosition) {

            return true;
        }

        return false;
    }

    public function isCredit() : bool
    {
        if ($this->positionMontant < $this->creditPosition) {

            return false;
        }

        return true;
    }


    public function isComplementaryInformations() : bool
    {
        if ($this->date == "") {
            return true;
        }

        return false;
    }

    public function addDetails(string $details) : void
    {
        $this->details .= PHP_EOL.$details;
    }

    private function setDate() : void
    {
        $date = static::getValue(
            $this->content,
            self::DATE_STARTING_POSITION,
            self::DATE_LENGTH
        );

        if (false === empty($date)) {
            $this->date = (\DateTimeImmutable::createFromFormat('d/m/Y', $date))->setTime(0,0,0);
        }
    }

    private function setValeur() : void
    {
        $valeur = static::getValue(
            $this->content,
            self::VALEUR_STARTING_POSITION,
            self::VALEUR_LENGTH
        );

        if (false === empty($valeur)) {
            $this->valeur = (\DateTimeImmutable::createFromFormat('d/m/Y', $valeur))->setTime(0, 0, 0);
        }
    }

    private function setMontant() : void
    {
        preg_match(self::MONTANT_PATTERN, $this->content, $montants, PREG_OFFSET_CAPTURE);
        if (count($montants)) {
            [$this->montant, $this->positionMontant] = $montants[0];
            $this->montant = AmoutFormatter::formatFloat($this->montant);
        }
    }

    private function setDetails() : void
    {
        $this->details = static::getValue(
            $this->content,
            self::MONTANT_STARTING_POSITION,
            strlen($this->content) - self::MONTANT_STARTING_POSITION - $this->positionMontant
        );
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDate(): ? \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValeur(): ? \DateTimeImmutable
    {
        return $this->valeur;
    }

    /**
     * @return string|null
     */
    public function getDetails(): ? string
    {
        return $this->details;
    }

    /**
     * @return float|null
     */
    public function getMontant(): ? float
    {
        return $this->montant;
    }

    private static function getValue(string $string, int $start, int $length = null) : string
    {
        if (null === $length) {
            $value = substr($string, $start);
        } else {
            $value = substr($string, $start, $length);
        }

        $value = trim($value);

        return $value;
    }

    public function guessType() : void
    {
        $this->type = OperationTypeGuesser::execute($this);
    }

    public function getType() : ? TypeInterface
    {
        return $this->type;
    }
}
