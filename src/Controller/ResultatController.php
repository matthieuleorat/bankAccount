<?php

namespace App\Controller;

use App\Entity\Category;
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
        $em =$this->getDoctrine()->getManager();
        $repo = $em->getRepository(Category::class);
        $htmlTree = $repo->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            array(
                'decorate' => true,
                'representationField' => 'slug',
                'html' => true
            )
        );

        $form = $this->createForm(ResultatType::class, null);

        $form->handleRequest($request);

        return $this->render('admin/resultat/index.html.twig', [
            'form' => $form->createView(),
            'htmlTree' => $htmlTree
        ]);
    }
}