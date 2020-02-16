<?php

namespace App\Controller\Admin;

use App\CategoryGuesser\CategoryGuesser;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

class DetailsToCategoryAdminController extends EasyAdminController
{
    public function applyAction()
    {
        $id = $this->request->query->get('id');
        /** @var DetailsToCategory $entity */
        $entity = $this->em->getRepository(DetailsToCategory::class)->find($id);

        /** @var Transaction[] $transactionWithoutCategories */
        $transactionWithoutCategories = $this->em->getRepository(Transaction::class)->findTransactionWithoutCategory();

        $count = 0;
        foreach ($transactionWithoutCategories as $transaction) {
            if (null !== $category = CategoryGuesser::execute($entity, $transaction->getDetails())) {
                $expense = new Expense();

                $expense->setCredit( $transaction->getCredit());
                $expense->setDebit($transaction->getDebit());
                $expense->setLabel($transaction->getDetails());
                $expense->setDate($transaction->getDate());
                $expense->setTransaction($transaction);
                $expense->setCategory($category);

                $this->em->persist($expense);
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
}