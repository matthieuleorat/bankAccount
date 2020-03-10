<?php

namespace App\Controller\Admin;

use App\CategoryGuesser\CategoryGuesser;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

class DetailsToCategoryAdminController extends EasyAdminController
{
    /**
     * @var CategoryGuesser
     */
    private $categoryGuesser;

    public function __construct(CategoryGuesser $categoryGuesser)
    {
        $this->categoryGuesser = $categoryGuesser;
    }

    public function applyAction()
    {
        $id = $this->request->query->get('id');
        /** @var DetailsToCategory $entity */
        $entity = $this->em->getRepository(DetailsToCategory::class)->find($id);

        /** @var Transaction[] $transactionWithoutCategories */
        $transactionWithoutCategories = $this->em->getRepository(Transaction::class)->findTransactionWithoutExpense();

        $count = 0;

        foreach ($transactionWithoutCategories as $transaction) {
            if (true === $this->categoryGuesser->execute($entity, $transaction)) {
                $expense = new Expense();
                $expense->setLabel($transaction->getTypeData($entity->getLabel()));
                $expense->setCategory($entity->getCategory());
                $expense->setTransaction($transaction);
                $expense->setDate($this->getAttributeValue($transaction, $entity->getDate()));
//                $expense->setCredit( $transaction->getCredit());
//                $expense->setDebit($transaction->getDebit());
//                dump($entity);
//                dump($entity->getLabel());



//                $this->em->persist($expense);
                $count++;
            }
        }

        $this->em->flush();

        $this->addFlash('success', $count . ' transactions trouvées');

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }

    private function getAttributeValue($object, string $attribute)
    {
        $tmp = explode('.', $attribute);

        $currentLvl = $object->{'get'.ucfirst($tmp[0])}();

        if (count($tmp) > 1) {
            array_shift($tmp);
            $testAsString = implode('.', $tmp);
            return $this->getAttributeValue($currentLvl, $testAsString);
        }

        return $object->{'get'.ucfirst($tmp[0])}();
    }
}