<?php

namespace App\Controller;

use App\Services\ArticleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    private ArticleService $articleService;
    public function __construct()
    {
        $this->articleService=new ArticleService();
    }
    #[Route('/user/article/create', name: 'article.create', methods:['Get'])]
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('article/create.html.twig', [
            'categories' => $this->articleService->create($entityManager)['categories'],
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/user/article/save', name: 'article.save', methods: ['POST'])]
    public function save (Request $request, EntityManagerInterface $entityManager)
    {
        $storage = $this->getParameter("kernel.project_dir") . "/file";
        return $this->redirectToRoute('article.create', [
            'data' => $this->articleService->save($request, $entityManager, $storage),
        ]);
    }
    #[Route('/user/article/show', name: 'article.show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager)
    {
       //dd($this->articleService->show($entityManager));

       return $this->render('article/show.html.twig',[
            'categories'=>$this->articleService->show($entityManager),
        ]);
    }
    #[Route('/user/article/edit/{id}', name: 'article.edit', methods: ['GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {
        //dd($this->articleService->edit($request, $entityManager));
        return $this->render('article/edit.html.twig',[
            'article'=>$this->articleService->edit($request, $entityManager)['article'],
            'categories'=>$this->articleService->edit($request, $entityManager)['categories'],
            'data'=>$request->get('data')

        ]);
    }
    #[Route('/user/article/update', name: 'article.update', methods: ['POST'])]
    public function update (Request $request, EntityManagerInterface $entityManager)
    {
        $storage = $this->getParameter("kernel.project_dir") . "/file";

        return $this->redirectToRoute("article.edit",[
            'id'=>$request->get('id'),
            'data'=>$this->articleService->update($request, $entityManager, $storage)
        ]);
    }
    #[Route('/user/article/delete/{id}', name: 'article.delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('article/delete.html.twig',[
            'result'=>$this->articleService->delete($request, $entityManager),
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/user/article/remove', name: 'article.remove', methods: ['POST'])]
    public function remove (Request $request, EntityManagerInterface $entityManager)
    {
        $storage = $this->getParameter("kernel.project_dir") . "/file";
        return $this->redirectToRoute('article.delete',[
            'id'=>$request->get('id'),
            'data'=>$this->articleService->remove($request, $entityManager, $storage)
        ]);
    }

    /**
     * Website, return All Articles by CategoryId Using Ajax
    */
    #[Route('/article/getAll', name: 'article.getAll', methods: ['POST'])]
    public function getAll(Request $request, EntityManagerInterface $entityManager)
    {
        //dd($this->articleService->getAll($request, $entityManager));
        return $this->json([
            'category'=>$this->articleService->getAll($request, $entityManager),
        ]);
    }


}
