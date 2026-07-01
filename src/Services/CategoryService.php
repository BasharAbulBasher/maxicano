<?php
namespace App\Services;

use App\Entity\ActicleScreen;
use App\Entity\Article;
use App\Entity\Category;
use App\Helper\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class CategoryService
{
    public function save(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Validation of the Title-Length
        if(strlen($request->get('title')) > 255)
        {
            return [
                'success' => false,
                'message' => 'The Length of Title is very long....',
            ];
        }
        $category= new Category();
        $category->setTitle($request->get('title'));
        //Set Nr
        $category->setNr($request->get('nr'));
        //Set Size
        if($request->get('size') !=null)
            {
                $category->setSize(true);
            }
        else{
                $category->setSize(false);
        }
        //Set Image
        if(!empty($request->files->get('image')))
            {
                $category->setImage($this->saveFile($storage, $request->files->get('image'), 'category'));
            }

        $entityManager->persist($category);
        $entityManager->flush();

        return[
            'success'=>true,
            'message'=>'Successfully created a new Category'
        ];
    }
    public function show(EntityManagerInterface $entityManager)
    {
        return $entityManager->getRepository(Category::class)->findAll();
    }
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {

        return $entityManager->getRepository(Category::class)->find($request->get('id'));
    }
    public function update (Request $request, EntityManagerInterface $entityManager, $storage)
    {
        $category=$entityManager->getRepository(Category::class)->find($request->get('id'));
        //Sett Title
        $category->setTitle($request->get('title'));
        //Set Nr
        $category->setNr($request->get('nr'));
        //Set Image if it exists
        if(!empty($request->files->get('image')))
            {
                //Remove the old Image from Storage, if it exists
                if(!empty($category->getImage()))
                    {
                        //Remove Old Image
                        $this->removeFile($storage, $category->getImage(), 'category');
                    }
                    //Save new Image
                    $category->setImage($this->saveFile($storage, $request->files->get('image'), 'category'));
            }
        //Set Size
        if($request->get('size') != null)
            {
                //Checkbox is clicked, it is NOT NULL
                $category->setSize(true);
            }
        else{
                //Checkbox is NOT clicked, it is NULL
                $category->setSize(false);
        }
        $entityManager->persist($category);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Successfully updated the Category...'
        ];
    }
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        //Find the Category by Id
        return $entityManager->getRepository(Category::class)->find($request->get('id'));
    }
    public function remove(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Find the Category
        $category=$entityManager->getRepository(Category::class)->find($request->get('id'));
        //Case Category Not Found
        if(empty($category))
            {
                return[
                    'success'=>false,
                    'message'=>'Category Not Found'
                ];
            }
        //Find ALL Articles of this Category
        $articles=$entityManager->getRepository(Article::class)->findAll();
        //Delete All Articles first
        if(!empty($articles))
            {
                foreach($articles as $article)
                    {
                        //Find All ArticleScreen 'Screens, they show this Article'
                        $articleScreens=$entityManager->getRepository(ActicleScreen::class)->findBy(['article'=>$article]);
                        //Delete All ArticleScreens first
                        if(!empty($articleScreens))
                            {
                                foreach($articleScreens as $articleScreen)
                                    {
                                        //Remove the ArticleScreen
                                        $entityManager->remove($articleScreen);
                                        $entityManager->flush();
                                    }
                            }
                        //Remove the Image of Article from Storage
                        $this->removeFile($storage, $article->getImage(), 'article');
                        //Remove the Article
                        $entityManager->remove($article);
                        $entityManager->flush();
                    }
            }
        //Finally remove the Category
        $entityManager->remove($category);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Success deleted the Category'
        ];
    }
    //Find if Category has Sizes
    public function hasSizes(Request $request, EntityManagerInterface $entityManager)
    {
        //Find the Category
        $category=$entityManager->getRepository(Category::class)->find($request->get('categoryId'));
        return $category->isSize();
    }
    private function saveFile($storage, UploadedFile $image, $dir_path)
    {
        $file=new File();
        $file->setFile($image);
        return $file->saveFile($storage, $dir_path);
    }
    private function removeFile($storage, $file_name, $dir_path)
    {
        if(!empty($file_name))
            {
                $file=new File();
                $file->removeFile($storage, $dir_path, $file_name);
            }
    }
    
}
