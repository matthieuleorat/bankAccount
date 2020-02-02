<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use Matleo\BankStatementParserBundle\BankStatementParser;
use Matleo\BankStatementParserBundle\Model\BankStatement;
use Matleo\BankStatementParserBundle\Model\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImportNewStatementController extends AbstractController
{
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

                $plainAccountNumber = $plainStatement->getAccountNumber();

                $entityManager = $this->getDoctrine()->getManager();

                $account = $entityManager->getRepository(Account::class)->findOneBy(['number' => $plainAccountNumber]);
                if (null === $account) {
                    $account = new Account();
                    $account->setName($plainAccountNumber);
                    $account->setNumber($plainAccountNumber);
                    $entityManager->persist($account);
                }

                $statement = $entityManager->getRepository(Statement::class)->findOneBy(['name' => $safeFilename]);
                if ($statement instanceof Statement) {
                    $this->addFlash('error', 'Un relevé de compte avec le même nom a déjà été importé');
                    return $this->redirectToRoute('import_new_statement');
                }

                $statement = new Statement();
                $statement->setAccount($account);
                $statement->setName($safeFilename);
                $statement->setTotalDebit($plainStatement->getDebit());
                $statement->setTotalCredit($plainStatement->getCredit());
                $statement->setStartingDate(\DateTimeImmutable::createFromFormat('d/m/Y',$plainStatement->getDateBegin()));
                $statement->setEndingDate(\DateTimeImmutable::createFromFormat('d/m/Y',$plainStatement->getDateEnd()));
                $statement->setStartingBalance($plainStatement->getSoldePrecedent());
                $statement->setEndingBalance($plainStatement->getNouveauSolde());
                $entityManager->persist($statement);

                $transactions = array_map(function(Operation $operation) use ($entityManager, $statement) {
                    $transaction = new Transaction();
                    $transaction->setDate(\DateTimeImmutable::createFromFormat('d/m/Y',$operation->getDate()));
                    $transaction->setDetails($operation->getDetails());
                    $transaction->setStatement($statement);
                    if ($operation->isDebit()) {
                        $transaction->setDebit($operation->getMontant());
                    }
                    if ($operation->isCredit()) {
                        $transaction->setCredit($operation->getMontant());
                    }
                    $entityManager->persist($transaction);

                    return $transaction;
                }, $plainStatement->getOperations());

                $entityManager->flush();

                $this->addFlash('success', 'Le relevé a bien été importé. '.count($transactions) .' ajoutées');
            }
        }

        return $this->render('import_new_statement/index.html.twig', [
            'controller_name' => 'ImportNewStatementController',
            'form' => $form->createView(),
        ]);
    }
}
