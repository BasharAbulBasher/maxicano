<?php

namespace App\Controller;

use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{

    #[Route('/cart/add', name: 'cart.add', methods:['POST'])]
    public function add(CartService $cartService, Request $request)
    {
        //$request->getSession()->remove('cart');

       $cartService->add($request->get("articleId"));
        return $this->json([
            'success'=>true,
            'cart'=>$request->getSession()->get('cart',[]),
        ]);
    }
    #[Route('/cart/delete', name: 'cart.delete', methods: ['POST'])]
    public function delete(CartService $cartService,Request $request)
    {
        $cartService->delete($request->get("index"));
        return $this->json([
            'success'=>true,
            'cart'=>$request->getSession()->get('cart',[]),
        ]);
    }
    #[Route('/cart/get', name: 'cart.get', methods: ['POST'])]
    public function get(CartService $cartService)
    {
        $articles=$cartService->get();
        return $this->json([
            'cart'=>$articles
        ]);
    }


}
