<?php

namespace App\Controller;

use mikehaertl\pdftk\Pdf;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class HomController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        $path = '/var/www/html/my_project_name/public/pdf/RCE_00057002074_20200108.pdf';

        $content = $this->getTextVersion($path);
        echo $content;
        exit;

        return $this->render('hom/index.html.twig', [
            'controller_name' => 'HomController',
            //'text' => $text,
        ]);
    }

    private function getTextVersion($filePath)
    {
        $tmpPath = 'pdf/' . rand(0, 10000) . '.txt';
        $process = new Process(['/usr/bin/pdftotext','-layout' , $filePath , $tmpPath]);

        $process->run(function ($type, $buffer) {

        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $content = file_get_contents($tmpPath);
        unlink($tmpPath);

        return $content;
    }
}
