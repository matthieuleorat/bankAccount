<?php

namespace App\Controller\Admin;

use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
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
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;

    public function __construct(CategoryGuesser $categoryGuesser, AttributeExtractor $attributeExtractor)
    {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
    }

    public function applyAction()
    {
        try {
            $id = $this->request->query->get('id');
            /** @var DetailsToCategory $entity */
            $entity = $this->em->getRepository(DetailsToCategory::class)->find($id);

            /** @var Transaction[] $transactionWithoutCategories */
            $transactionWithoutCategories = $this->em->getRepository(Transaction::class)->findTransactionWithoutExpense();

            $count = 0;

            foreach ($transactionWithoutCategories as $transaction) {

                if (true === $this->categoryGuesser->execute($entity, $transaction)) {
                    $expense = new Expense();
                    $expense->setLabel($this->attributeExtractor->extract($transaction, $entity->getLabel()));
                    $expense->setCategory($entity->getCategory());
                    $expense->setTransaction($transaction);
                    $expense->setDate($this->attributeExtractor->extract($transaction, $entity->getDate()));
                    $expense->setCredit($this->attributeExtractor->extract($transaction, $entity->getCredit()));
                    $expense->setDebit($this->attributeExtractor->extract($transaction, $entity->getDebit()));
                    $this->em->persist($expense);
                    $count++;
                }
            }

            $this->em->flush();

            $this->addFlash('success', $count . ' transactions trouvées');
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => 'DetailsToCategory',
            ));
        }

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }
}