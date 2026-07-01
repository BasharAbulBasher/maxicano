<?php
namespace App\Services;

use App\Entity\ActicleScreen;
use App\Entity\Article;
use App\Entity\Category;
use App\Helper\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ArticleService
{
    public function create(EntityManagerInterface $entityManager)
    {
        return [
            'categories'=>$entityManager->getRepository(Category::class)->findAll(),
        ];
    }
    public function save(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Validation
        if(!empty($this->valid($entityManager, $request)))
        {
            return [
                'success'=>false,
                'message'=>'Please Controlle your Data again',
                'err'=>$this->valid($entityManager, $request)
            ];
        }
        //Init $article
        $article=new Article();
        //Set Category
        $article->setCategory($entityManager->getRepository(Category::class)->find($request->get('categoryId')));
        //Set Description if it exist
        if(!empty($request->get('description')))
        {
            $article->setDescription($request->get('description'));
        }
        //Set Title
        $article->setTitle($request->get('title'));
        //Set Nr
        if(!empty($request->get('nr')))
            {
                $article->setNr($request->get('nr'));
            }
        //Set Image
        $article->setImage($this->saveFile($request->files->get('image'), $storage));
        //Set Big-Price
        if(!empty($request->get('bigPrice')))
            {
                $article->setPrice($request->get('bigPrice'));
            }
        //Set Small-Price
        if(!empty($request->get($request->get('smallPrice'))))
            {
                $article->setSmallPrice($request->get('smallPrice'));
            }

        $entityManager->persist($article);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Successfully created an new Article'
        ];
    }

    public function show(EntityManagerInterface $entityManager)
    {
        $categories=[];
        foreach($entityManager->getRepository(Category::class)->findAll() as $category)
            {
                array_push($categories, $category->toArray());
            }
            return $categories;
    }
    public function edit (Request $request, EntityManagerInterface $entityManager)
    {
        $categories_arr=[];
        //All Categories to DTO
        foreach($entityManager->getRepository(Category::class)->findAll() as $category)
            {
                array_push($categories_arr, $category->toArray());
            }
        return[
                'article'=>$entityManager->getRepository(Article::class)->find($request->get('id'))->toArray(),
                'categories'=>$categories_arr,
        ];
    }

    public function update(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Validation
        if(!empty($this->valid($entityManager, $request)))
        {
            return [
                    'success' => false,
                    'message' => 'Please Controlle your Data again',
                    'err'     => $this->valid($entityManager, $request),
                ];
        }
        //Find The Article
        $article=$entityManager->getRepository(Article::class)->find($request->get('id'));
        //The Article IS NOT FOUND
        if(empty($article))
            {
                return [
                    'success'=>false,
                    'message'=>'Article Not Found'
                ];
            }
        //Set Nr
        if(!empty($request->get('nr')))
        {
            $article->setNr($request->get('nr'));
        }else{
            $article->setNr(null);
        }
        //Set Title
        $article->setTitle($request->get('title'));
        //Set Description if it exists
        if(!empty($request->get('description')))
            {
                $article->setDescription($request->get('description'));
            }
        //Set new Image if it exists
        if(!empty($request->files->get('image')))
            {
                //remove the old Image
                  $this->removeFile($article->getImage(), $storage);
                //Set the new Image
                $article->setImage($this->saveFile($request->files->get('image'), $storage));
            }
        //Set Big-Price
        if (!empty($request->get('bigPrice'))) {
            $article->setPrice($request->get('bigPrice'));
        }
        else{
            $article->setPrice(null);
        }
        //Set Small-Price
        if (!empty($request->get('smallPrice'))) {
            $article->setSmallPrice($request->get('smallPrice'));
        }
        else{
            $article->setSmallPrice(null);
        }


        $entityManager->persist($article);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Successfully updated the Article'
        ];
    }

    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        $article= $entityManager->getRepository(Article::class)->find($request->get('id'));
        if(!empty($article))
        {
            return[
                'article'=>$article,
                'success'=>'true',
            ];
        }
        return[
            'success'=>false,
            'message'=>'Article Not FOUND.....'
        ];
    }

    public function remove(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Find Article
        $article=$entityManager->getRepository(Article::class)->find($request->get('id'));
        if(empty($article))
                {
            return[
                'success'=>false,
                'message'=>'The Article is NOT FOUND..'
            ];
            }
            //Get All Screens, they show this Article
            $articleInScreens=$entityManager->getRepository(ActicleScreen::class)->findBy(['article'=>$article]);
            //Remove All ArticleScreen
            if(!empty($articleInScreens))
                {
                    foreach ($articleInScreens as $articleInScreen) {
                        $entityManager->remove($articleInScreen);
                        $entityManager->flush();
                    }
                }

            //Remove the Image
            $this->removeFile($article->getImage(), $storage);
            //Delete the Article
            $entityManager->remove($article);
            $entityManager->flush();
            return[
                'success'=>true,
                'message'=>'The Article was deleted Successfuly'
            ];


    }

    /**
     * Website,
     *Get All Articles By One CategoryId, Response to Ajax-Request
     */
    public function getAll(Request $request, EntityManagerInterface $entityManager)
    {
        $category=$entityManager->getRepository(Category::class)->find($request->get('categoryId'));
        return $category->toArray();
    }


    //Private Functions----
    private function valid(EntityManagerInterface $entityManager,Request $request)
    {
        $err=[];
        //Title Length Validation
        if(strlen($request->get('title')) > 255)
        {
            $err['title']='The Title is very long';
        }
        //Image Type Validation
        $image=$request->files->get('image');
        if(!empty($image))
        {
            if(!getimagesize($image))
            {
                $err['image']="The Image musst be .jpg- or .npg- Data-Type";
            }
        }
        //Valid if Category is selected
            if($request->get("categoryId")==0)
            {
                $err['categoryId']="Please choose an Category for this Article";
            }
            //Valid if Category is NOT in DB
            if (empty($entityManager->getRepository(Category::class)->find($request->get('categoryId')))) {
                $err['categoryId'] = "Please choose a Category for this Article";
            }

        //Validation of Nr, Nr can be empty. if the Nr is not Empty, then muss it be Uniq
        if(!empty($request->get('nr')))
        {
            if(!empty($entityManager->getRepository(Article::class)->findOneBy(['nr'=>$request->get('nr')])))
            {
                //Create Case
                if(empty($request->get('id')))
                {
                    $err['nr']="Another Article has this Number '". $request->get('nr') . "', Please change it..";
                }
                //Update Case
                else{
                    $foundArticle=$entityManager->getRepository(Article::class)->findOneBy(['nr' => $request->get('nr')]);
                    if($foundArticle->getId() != $request->get('id'))
                    {
                        $err['nr'] = "Another Article has this Number '" . $request->get('nr') . "', Please change it..";
                    }
                }
            }
        }

        //Price Validation,
        if(empty($request->get('smallPrice')) && empty($request->get('bigPrice')))
            {
                $err['price']="Do not vorget the Price Please";
            }

        return $err;
    }
    private function saveFile(UploadedFile $file, $storage)
    {
        $dir_path="article";
        $image_video=new File();
        $image_video->setFile($file);
       return $image_video->saveFile($storage, $dir_path);
    }

    private function removeFile($file_name, $storage)
    {
        $dir_path="article";
        $image_video=new File();
        $image_video->removeFile($storage, $dir_path, $file_name);
    }
}
