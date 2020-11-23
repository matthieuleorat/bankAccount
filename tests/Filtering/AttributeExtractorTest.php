<?php declare(strict_types=1);

namespace App\Tests\Filtering;

use App\Entity\Transaction;
use App\Filtering\AttributeExtractor;
use BankStatementParser\Model\TransferReceived;
use PHPUnit\Framework\TestCase;

class AttributeExtractorTest extends TestCase
{
    const TRANSFERT_RECEIVED = "VIR RECU 9936053227134".
    "\nDE: MLLE GEGE RALDINE".
    "\nMOTIF: complement";

    public function testExtract() : void
    {
        $transaction = new Transaction();
        $transaction->setDetails('transaction details');

        preg_match(TransferReceived::PATTERN, self::TRANSFERT_RECEIVED, $matches);
        $type = TransferReceived::create($matches);
        $transaction->setType($type);

        $attributeExtractor = new AttributeExtractor();
        $extractedAttributeDetails = $attributeExtractor->extract($transaction, 'details');
        $this->assertEquals('transaction details', $extractedAttributeDetails);

        $extractedAttributeTypeFrom = $attributeExtractor->extract($transaction, 'type.from');
        $this->assertEquals('MLLE GEGE RALDINE', $extractedAttributeTypeFrom);

        $extractedUnexistingAttribute = $attributeExtractor->extract($transaction, 'unexistingAttribute');
        $this->assertNull($extractedUnexistingAttribute);
    }
}
