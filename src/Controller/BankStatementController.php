<?php

namespace App\Controller;

use App\BankStatementParser\BankStatementParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BankStatementController extends AbstractController
{
    /**
     * @Route("/bank/statement", name="bank_statement")
     */
    public function index(BankStatementParser $bankStatementParser)
    {
        $fileName = 'RCE_00057002074_20200108.pdf';

        $bankStatement = $bankStatementParser->execute($fileName);

        dump($bankStatement);die;
        return $this->render('bank_statement/index.html.twig', [
            'controller_name' => 'BankStatementController',
        ]);
    }
}
