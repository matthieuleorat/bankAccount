<?php

namespace App\Controller;

use \Matleo\BankStatementParserBundle\BankStatementParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * @Route("/home", name="home")
     *
     * @param BankStatementParser $bankStatementParser
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function index(BankStatementParser $bankStatementParser)
    {
        $filename = "RCE_00057002074_20200108.pdf";
        $path = "/var/www/html/public/";

        $obj = $bankStatementParser->execute($filename, $path);

        dump($obj);exit;

        return $this->render('hom/index.html.twig', [
            'text' => 'HomController',
        ]);
    }
}
