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

class PermanentTransfert extends AbstractType
{
    const NAME = 'permanent_transfert';
    const PATTERN = '/^\d+\s{1}VIR\sPERM\nPOUR:\s(.*)\nREF:\s(\d*)\nMOTIF:\s(.*)\nLIB:\s(.*)$/s';

    /**
     * @var string
     */
    private $recepient;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $label;

    private function __construct()
    {
    }

    public static function create(array $matches) : TypeInterface
    {
        list(, $recepient, $reference, $reason, $label) = $matches;
        $obj = new self();
        $obj->recepient = $recepient;
        $obj->reference = $reference;
        $obj->reason = $reason;
        $obj->label = $label;

        return $obj;
    }

    /**
     * @return string
     */
    public function getRecepient(): string
    {
        return $this->recepient;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
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
    public function getLabel(): string
    {
        return $this->label;
    }
}
