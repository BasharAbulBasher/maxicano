<?php
namespace App\Services;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager= $entityManager;
    }

    //Add To Cart
    public function add($articleId)
    {
        $article=$this->entityManager->getRepository(Article::class)->find($articleId);
        if(!empty($article))
            {
                //Get the Seesion
                $session=$this->requestStack->getSession();
                //Get the Cart from the Session
                $cart=$session->get("cart",[]);
                //Push the Article in Cart
                array_push($cart,$articleId);
                //Set the Cart in the Session agin
                $session->set("cart",$cart);
            }
    }

    //Delete from Cart
    public function delete($index)
    {

        //Get the Session
        $session=$this->requestStack->getSession();
        //Get the Cart from Session
        $cart=$session->get('cart',[]);
        //Remove the Article from Cart
        array_splice($cart,$index,1);
        //Set the Cart in Seesion agin
        $session->set('cart', $cart);
    }

    //Get the Cart
    public function get()
    {
        $articles=[];
        //Get the Session
       $session=$this->requestStack->getSession();
       //Get the Cart from Session
       $cart=$session->get('cart',[]);
       foreach($cart as $articleId)
        {
            $article=$this->entityManager->getRepository(Article::class)->find($articleId);
            array_push($articles, $article->toArray());
        }
        return $articles;
    }
}
