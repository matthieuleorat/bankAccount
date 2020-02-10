<?php

namespace App\Controller;

use App\CategoryGuesser\CategoryGuesser;
use App\Entity\Source;
use App\Entity\Category;
use App\Entity\DetailsToCategory;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use Matleo\BankStatementParserBundle\BankStatementParser;
use Matleo\BankStatementParserBundle\Model\BankStatement;
use Matleo\BankStatementParserBundle\Model\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
                'date' => \DateTimeImmutable::createFromFormat('d/m/Y',$operation->getDate()),
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

            $category = $this->categoryGuesser($operation->getDetails());

            if ($category instanceof Category) {

            }
        }

        $transaction->setDetails($operation->getDetails());

        $this->entityManager->persist($transaction);

        return $transaction;
    }

    private function categoryGuesser(string $details) : ? Category
    {
        $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findAll();

        /** @var DetailsToCategory $filter */
        foreach ($filters as $filter) {
            if (null !== $category = CategoryGuesser::execute($filter, $details)) {
                return $category;
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
