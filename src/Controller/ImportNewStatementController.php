<?php

namespace App\Controller;

use App\Filtering\CategoryGuesser;
use App\Entity\Expense;
use App\Entity\Source;
use App\Entity\Category;
use App\Entity\DetailsToCategory;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use Matleo\BankStatementParser\BankStatementParser;
use Matleo\BankStatementParser\Model\BankStatement;
use Matleo\BankStatementParser\Model\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImportNewStatementController extends AbstractController
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $entityManager;

    /**
     * @Route("/import/new/statement", name="import_new_statement")
     * @param BankStatementParser $bankStatementParser
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(BankStatementParser $bankStatementParser, Request $request)
    {
        $form = $this->createForm(ImportStatementType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $statementFile */
            $statementFile = $form->get('statement')->getData();

            if ($statementFile instanceof UploadedFile) {

                $originalFilename = pathinfo($statementFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);

                /** @var BankStatement $plainStatement */
                $plainStatement = $bankStatementParser->execute($statementFile->getFilename(), $statementFile->getpath().'/');

                $this->entityManager = $this->getDoctrine()->getManager();

                $account = $this->handleAccount($plainStatement->getAccountNumber());

                $statement = $this->handleStatement($account, $plainStatement, $safeFilename);
                if ($statement instanceof RedirectResponse) {
                    return $statement;
                }

                $transactions = array_map(function(Operation $operation) use ($statement) {
                    return $this->transformOperationIntoTransaction($operation, $statement);
                }, $plainStatement->getOperations());

                $this->entityManager->flush();

                $this->addFlash('success', 'Le relevé a bien été importé. '.count($transactions) .' ajoutées');
            }
        }

        return $this->render('admin/import_new_statement/index.html.twig', [
            'controller_name' => 'ImportNewStatementController',
            'form' => $form->createView(),
        ]);
    }

    private function handleStatement(Source $account, BankStatement $bankStatement, string $filename)
    {
        $statement = $this->entityManager->getRepository(Statement::class)->findOneBy(['name' => $filename]);

        if ($statement instanceof Statement) {
            return $statement;
        }

        $statement = new Statement();
        $statement->setSource($account);
        $statement->setName($filename);
        $statement->setTotalDebit($bankStatement->getDebit());
        $statement->setTotalCredit($bankStatement->getCredit());
        $statement->setStartingDate(\DateTimeImmutable::createFromFormat('d/m/Y', $bankStatement->getDateBegin()));
        $statement->setEndingDate(\DateTimeImmutable::createFromFormat('d/m/Y', $bankStatement->getDateEnd()));
        $statement->setStartingBalance($bankStatement->getSoldePrecedent());
        $statement->setEndingBalance($bankStatement->getNouveauSolde());
        $this->entityManager->persist($statement);

        return $statement;
    }

    private function transformOperationIntoTransaction(Operation $operation, Statement $statement) : Transaction
    {
        $transaction = $this->entityManager->getRepository(Transaction::class)->findOneBy(
            [
                'statement' => $statement,
                'date' => $operation->getDate(),
                'debit' => $operation->isDebit() === true ? $operation->getMontant() : null,
                'credit' => $operation->isCredit() === true ? $operation->getMontant() : null
            ]
        );

        if (false === $transaction instanceof Transaction) {
            $transaction = new Transaction();
            $transaction->setDate(\DateTimeImmutable::createFromFormat('d/m/Y',$operation->getDate()));
            $transaction->setStatement($statement);


            if ($operation->isDebit()) {
                $transaction->setDebit($operation->getMontant());
            }
            if ($operation->isCredit()) {
                $transaction->setCredit($operation->getMontant());
            }

//            $detailsToCategory = $this->categoryGuesser($transaction);
//
//            if ($detailsToCategory instanceof Category) {
//                $expense = new Expense();
//                $expense->setDate(\DateTimeImmutable::createFromFormat('d/m/Y',$operation->getDate()));
//                $expense->setLabel($operation->getDetails());
//                $expense->setCategory($detailsToCategory->getCategory());
//                $expense->setTransaction($transaction);
//                $expense->setCredit($transaction->getCredit());
//                $expense->setDebit($transaction->getDebit());
//                $this->entityManager->persist($expense);
//            }
        }

        if (null !== $operation->getType()) {
            $transaction->setType($operation->getType());
        }


        $transaction->setDetails($operation->getDetails());

        $this->entityManager->persist($transaction);

        return $transaction;
    }

    private function categoryGuesser(Transaction $transaction) : ? DetailsToCategory
    {
        $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findAll();

        /** @var DetailsToCategory $filter */
        foreach ($filters as $filter) {
            if (true === CategoryGuesser::execute($filter, $transaction)) {
                return $filter;
            }
        }

        return null;
    }

    private function handleAccount(string $accountNumber) : Source
    {
        $account = $this->entityManager->getRepository(Source::class)->findOneBy(['number' => $accountNumber]);

        if (null === $account) {
            $account = new Source();
            $account->setName($accountNumber);
            $account->setNumber($accountNumber);
            $this->entityManager->persist($account);
        }

        return $account;
    }
}
