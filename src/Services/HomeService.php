<?php
namespace App\Services;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class HomeService
{
    public function user(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->getCategories($entityManager);
    }
    public function save(Request $request, EntityManagerInterface $entityManager)
    {
        //Find The Article
        $article=$entityManager->getRepository(Article::class)->find($request->get(('articleId')));
        $is_spezial=false;
        $is_video=false;
        if($request->get('is_spezial')== 'true')
        {
            $is_spezial=true;
        }
        if($request->get('is_video')== 'true')
        {
            $is_video=true;
        }
        //Setter
        $article->setIsVideo($is_video);
        $article->setIsSpezial($is_spezial);
        $entityManager->persist($article);
        $entityManager->flush();
        return "Successfully saved";
    }
    public function kellner(EntityManagerInterface $entityManager)
    {
        return $this->getCategories($entityManager);
    }
    private function getCategories(EntityManagerInterface $entityManager)
    {
        $categories_arr = [];
        foreach ($entityManager->getRepository(Category::class)->findAll() as $category) {
            array_push($categories_arr, $category->toArray());
        }
        return $categories_arr;
    }
}
