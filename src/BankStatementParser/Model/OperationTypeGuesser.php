<?php declare(strict_types=1);

namespace BankStatementParser\Model;

class OperationTypeGuesser
{
    /**
     * @var TypeInterface[]
     */
    const OPERATION_TYPES = [
        CreditCardPayment::class,
        EuropeanDirectDebit::class,
        PermanentTransfert::class,
        TransferReceived::class,
        TransferSended::class,
        HomeLoan::class,
    ];

    public static function execute(Operation $operation) : ? TypeInterface
    {
        foreach (self::OPERATION_TYPES as $type) {
            $obj = $type::createFormOperation($operation);
            if ($obj instanceof TypeInterface) {
                return $obj;
            }
        }

        return null;
    }
}
