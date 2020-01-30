<?php

namespace App\Controller;

use Matleo\BankStatementParserBundle\BankStatementParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(BankStatementParser $bankStatementParser)
    {

        return $this->render('hom/index.html.twig', [
            'text' => 'HomController',
        ]);
    }
}
