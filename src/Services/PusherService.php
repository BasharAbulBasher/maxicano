<?php
namespace App\Services;

use App\Entity\ActicleScreen;
use App\Entity\Category;
use App\Entity\Screen;
use App\Entity\ScreenWerbung;
use Doctrine\ORM\EntityManagerInterface;

class PusherService{

    public function push(EntityManagerInterface $entityManager)
    {
        return[
            //Get Categories in Tv1
            'tv1'=>$this->getCategories($entityManager,'tv1'),
            //Get Categories in Tv2
            'tv2'=>$this->getCategories($entityManager, 'tv2'),
            //Get Categories in Tv3
            'tv3'=>$this->getCategories($entityManager, 'tv3'),
            //Get Werbungen in Tv1
            'tv1Werbungen'=>$this->getWerbungen($entityManager, 'tv1'),
            //Get Werbungen in Tv2
            'tv2Werbungen' => $this->getWerbungen($entityManager, 'tv2'),
            //Get Werbungen in Tv3
            'tv3Werbungen' => $this->getWerbungen($entityManager, 'tv3'),
        ];

    }
    /**
     * Get All Categories by Screen
     */
    private function getCategories(EntityManagerInterface $entityManager, $screenTitle)
    {
        $categories_arr = [];
        //Get the Screen
        $screen = $entityManager->getRepository(Screen::class)->findOneBy(['title' => $screenTitle]);
        //Get All Articles by Screen Using visit Method in ArticleScreen-Repository
        $articles = $entityManager->getRepository(ActicleScreen::class)->visit($screen->getId());
        //Get All Categories
        $categories = $entityManager->getRepository(Category::class)->findAll();
        foreach ($categories as $category) {
            $category_arr = [];
            foreach ($articles as $article) {
                if ($article['categoryId'] == $category->getId()) {
                    array_push($category_arr, $article);
                }
            }
            if (! empty($category_arr)) {
                array_push($categories_arr, $category_arr);

            }

        }
        return $categories_arr;

    }
    /**
     * Get 'Werbungen' by Screen
     */
    private function getWerbungen(EntityManagerInterface $entityManager, $screenTitle)
    {
        $werbungen_arr=[];
        //Get Screen by Title
        $screen=$entityManager->getRepository(Screen::class)->findOneBy(['title'=>$screenTitle]);
        //Get All ScreensWerbungen
        $screensWerbungen=$entityManager->getRepository(ScreenWerbung::class)->findAll();
        //Loop to get All 'Werbungen' Just by Screen
        foreach($screensWerbungen as $screenWerbung)
            {
                if($screenWerbung->getScreen() == $screen)
                    {
                        array_push($werbungen_arr, $screenWerbung->getWerbung()->toArray());
                    }
            }
        return $werbungen_arr;
    }

}
