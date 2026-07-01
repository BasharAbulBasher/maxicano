<?php
namespace App\Services;

use App\Entity\ActicleScreen;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Screen;
use App\Entity\ScreenWerbung;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ScreenService
{
    public function save(Request $request, EntityManagerInterface $entityManager)
    {
        //Init Screen
        $screen= new Screen();
        //Setter
        $screen->setTitle($request->get('title'));
        //Prepare the Object
        $entityManager->persist($screen);
        //Execute the SQL-STATMENT
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Successfully created new Screen'
        ];
    }

    public function show(EntityManagerInterface $entityManager)
    {
        return $entityManager->getRepository(Screen::class)->findAll();
    }
    public function edit(EntityManagerInterface $entityManager, $id)
    {
        return $entityManager->getRepository(Screen::class)->find($id);
    }

    public function update(EntityManagerInterface $entityManager, Request $request)
    {
        //Find the Screen
        $screen=$entityManager->getRepository(Screen::class)->find($request->get('id'));
        //Setter
        $screen->SetTitle($request->get('title'));
        //Prepare the Object
        $entityManager->persist($screen);
        //Execute the SQL-STATMENT
        $entityManager->flush();
        return [
            'success'=>true,
            'message'=>'Successfuly updated the Screen'
        ];
    }
    //Delete
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        //Find the Screen
        return $entityManager->getRepository(Screen::class)->find($request->get('id'));
    }
    //Remove
    public function remove(Request $request, EntityManagerInterface $entityManager)
    {
        //Find the Screen
        $screen= $entityManager->getRepository(Screen::class)->find($request->get('id'));
        if(empty($screen))
            {
                return [
                    'success' => false,
                    'message' => 'The Screen is NOT FOUND',
                ];
            }
        //Remove the Screen
        $entityManager->remove($screen);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Success deleted the Screen'
        ];
    }

    // Create Articles In Screen
    public function createArticleInScreen(Request $request, EntityManagerInterface $entityManager)
    {
        //Find All Categories in 'DTO'
        $categories_arr=[];
        foreach($entityManager->getRepository(Category::class)->findAll() as $category)
            {
                array_push($categories_arr, $category->toArray());
            }
        //Find Screen by Tittle
        $screen=$entityManager->getRepository(Screen::class)->findOneBy(['title'=>$request->get('screenTitle')]);
        //Find All ArticlesScreen
        $articlesScreen=$entityManager->getRepository(ActicleScreen::class)->findBy(['screen'=>$screen]);
        return[
            'categories'=>$categories_arr,
            'screen'=>$screen,
            'articlesScreen'=>$articlesScreen
        ];
    }

    //Save Articles In Screen
    public function saveArticleInScreen(Request $request, EntityManagerInterface $entityManager)
    {
        //Find the Screen
        $screen=$entityManager->getRepository(Screen::class)->find($request->get('screenId'));
        //Screen Not Found
        if(empty($screen))
            {
                return[
                    'success'=>false,
                    'message'=>'Screen Not Found'
                ];
            }
        //Find All ArticlesScreen
        $articlesScreen=$entityManager->getRepository(ActicleScreen::class)->findBy(['screen'=>$screen]);
        //Delete All ArticleScreen
        if(!empty($articlesScreen))
            {
                //Loop All ArticlesScreen
                foreach($articlesScreen as $articleScreen)
                    {
                        //Delete ArticleScreen
                        $entityManager->remove($articleScreen);
                        $entityManager->flush();
                    }
            }
        //Loop All Articles
        foreach($entityManager->getRepository(Article::class)->findAll() as $article)
            {
                if(!empty($request->get($article->getId())))
                    {
                        //Save new ArticleScreen
                        $articleScreen=new ActicleScreen();
                        //Set Article
                        $articleScreen->setArticle($article);
                        //Set Screen
                        $articleScreen->setScreen($screen);
                        //Save
                        $entityManager->persist($articleScreen);
                        $entityManager->flush();
                    }
            }
        //return success
        return[
            'success'=>true,
            'message'=>'Success Saved'
        ];
    }
    public function view(Request $request, EntityManagerInterface $entityManager)
    {
        //Get Screen By Title
        $screen=$entityManager->getRepository(Screen::class)->findOneBy(['title'=>$request->get('screenTitle')]);
        //Screen Found success
        if(!empty($screen))
            {
                return[
                    'success'=>true,
                    'message'=>'success found the Screen',
                    'screenTitle'=>$request->get('screenTitle'),
                ];
            }
        //Screen NOT Found
        $screenTitle=$request->get('screenTitle');
        return [
            'success' => false,
            'message' => "Screen $screenTitle is NOT FOUND",

            ];

    }
    /**
     * Get All Categories und Werbungen by Screen
     */
    public function visit(Request $request, EntityManagerInterface $entityManager)
    {
        return [
            'categories'=>$this->getCategories($request, $entityManager),
            'werbungen'=>$this->getWerbungen($request, $entityManager),
        ];
    }

    /**
     * Get All Categories by Screen
     */
    private function getCategories(Request $request, EntityManagerInterface $entityManager)
    {
        $categories_arr=[];
        //Get the Screen
        $screen = $entityManager->getRepository(Screen::class)->findOneBy(['title' => $request->get('screenTitle')]);
        //$screen = $entityManager->getRepository(Screen::class)->findOneBy(['title' => 'tv1']);

        //Get All Articles by Screen Using visit Method in ArticleScreen-Repository
        $articles=$entityManager->getRepository(ActicleScreen::class)->visit($screen->getId());
        //Get All Categories
        $categories=$entityManager->getRepository(Category::class)->findAll();
        foreach($categories as $category)
            {
                $category_arr=[];
                foreach($articles as $article)
                    {
                        if($article['categoryId']==$category->getId())
                            {
                                array_push($category_arr, $article);
                            }
                    }
                if(!empty($category_arr))
                    {
                        array_push($categories_arr, $category_arr);

                    }

            }
        return $categories_arr;
    }
    /**
     * Get All 'Werbungen' by Screen
     */
    private function getWerbungen(Request $request, EntityManagerInterface $entityManager)
    {
        $werbungen_arr=[];
        //Get Screen by Title
        $screen=$entityManager->getRepository(Screen::class)->findOneBy(['title'=>$request->get('screenTitle')]);
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
