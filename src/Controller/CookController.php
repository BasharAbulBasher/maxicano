<?php

namespace App\Controller;

use App\Services\CookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CookController extends AbstractController
{
    /**
     * Return the Current View
     */
    #[Route('/cookrole/cook/current', name: 'cook.current', methods:['GET'])]
    public function current()
    {
        return $this->render('cook/current.html.twig', [
            'controller_name' => 'CookController',
        ]);
    }
    /**
     * Return the Finish View
     */
    #[Route('/cookrole/cook/finish', name: 'cook.finish', methods:['GET'])]
    public function finish()
    {
        return $this->render('cook/finish.html.twig');
    }
        /**
     * Return the Setting View
     */
    #[Route('/cookrole/cook/setting', name: 'cook.setting', methods: ['GET'])]
    public function setting(CookService $cookService)
    {
            //dd($cookService->setting());
        return $this->render('cook/setting.html.twig',[
            'categories'=>$cookService->setting()
        ]);
    }
    /**
     * Set the Article in SoldOut; SoldOut=true 'Ausgekauft'
     */
    #[Route('/cookrole/cook/soldOut', name: 'cook.soldOut', methods: ['Post'])]
    public function soldOut(CookService $cookService, Request $request)
    {
        return $this->json([
            'data'=>$cookService->sold($request->get('articleId'), true),
        ]);
    }
    /**
     * Set the Article in SoldIn; SoldOut=false 'NICHT Ausgekauft'
     */
    #[Route('/cookrole/cook/soldIn', name: 'cook.soldIn', methods: ['Post'])]
    public function soldIn(CookService $cookService, Request $request)
    {
        return $this->json([
            'data' => $cookService->sold($request->get('articleId'), false),
        ]);

    }
}
