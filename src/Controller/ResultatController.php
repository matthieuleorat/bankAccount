<?php

namespace App\Controller;

use App\Form\ResultatType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResultatController extends EasyAdminController
{
    /**
     * @Route("/resultat", name="resultat")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(ResultatType::class, null);

        $form->handleRequest($request);

        return $this->render('admin/resultat/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}