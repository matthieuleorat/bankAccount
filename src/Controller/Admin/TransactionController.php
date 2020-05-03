<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends EasyAdminController
{
    public function toggleIgnoreAction() : Response
    {
        $id = $this->request->query->get('id');

        /** @var Transaction $entity */
        $entity = $this->em->getRepository(Transaction::class)->find($id);

        $value = true;

        if ($entity->isIgnore()) {
            $value = false;
        }

        $entity->setIgnore($value);

        $this->em->flush();

        $this->addFlash('success', 'Modification enrengitrée');

        return $this->redirectToRoute('easyadmin', [
            'entity' => 'Transaction',
            'action' => 'list',
        ]);
    }
}
