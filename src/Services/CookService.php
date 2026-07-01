<?php
namespace App\Services;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class CookService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * Setting
     * Get All Categories
     */
    public function setting()
    {
        $categories_arr=[];
        //Get All Categories
        $categories=$this->entityManager->getRepository(Category::class)->findAll();
        //Loop Categories
        foreach($categories as $category)
            {
                array_push($categories_arr, $category->toArray());
            }
        return $categories_arr;
    }
    /**
     * Sold In/Out; set the SoldOut of en Article
     */
    public function sold($articleId, bool $sold)
    {
        //Get the Article
        $article=$this->entityManager->getRepository(Article::class)->find($articleId);
        $article->setSoldOut($sold);
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        return 'success';
    }
}
