<?php

namespace App\Controller;

use App\Services\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    private CategoryService $categoryService;
    public function __construct()
    {
        $this->categoryService= new CategoryService();
    }
    #[Route('/admin/category/create', name: 'category.create', methods:['GET'])]
    public function create(Request $request)
    {
        return $this->render('category/create.html.twig', [
            'data' => $request->get('data'),
        ]);
    }
    #[Route('/admin/category/save', name: 'category.save', methods:['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager)
    {
        $storage=$this->getParameter('kernel.project_dir').'/file';
        return $this->redirectToRoute('category.create', [
            'data' =>$this->categoryService->save($request, $entityManager, $storage)
        ]);

    }
    #[Route('/admin/category/show', name: 'category.show', methods: ['GET'])]
    public function show (EntityManagerInterface $entityManager)
    {
        return $this->render('category/show.html.twig', [
            'categories' => $this->categoryService->show($entityManager),
        ]);
    }
    #[Route('/admin/category/edit/{id}', name: 'category.edit', methods: ['GET'])]
    public function edit (Request $request,EntityManagerInterface $entityManager)
    {
        return $this->render('category/edit.html.twig', [
            'category' => $this->categoryService->edit($request, $entityManager),
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/admin/category/update', name: 'category.update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager)
    {
        $storage= $this->getParameter('kernel.project_dir') .'/file';
        return $this->redirectToRoute('category.edit', [
            'id'=>$request->get('id'),
            'data' => $this->categoryService->update($request, $entityManager, $storage),
        ]);
    }
    #[Route('/admin/category/delete/{id}', name: 'category.delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('category/delete.html.twig',[
            'category'=>$this->categoryService->delete($request, $entityManager),
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/admin/category/remove', name: 'category.remove', methods: ['POST'])]
    public function remove(Request $request, EntityManagerInterface $entityManager)
    {
        $storage= $this->getParameter('kernel.project_dir') . '/file';

       return $this->redirectToRoute('category.delete',[
            'id'=>$request->get('id'),
            'data'=>$this->categoryService->remove($request, $entityManager, $storage)
       ]);
    }
    //Find if Category has Sizes
    #[Route('/user/category/hasSizes', name: 'category.hasSizes', methods: ['POST'])]
    public function hasSizes(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->json([
            'isSize'=>$this->categoryService->hasSizes($request, $entityManager)
        ]);
    }
    /**
     * Home Page for the Website
     */
    #[Route('/category/getAll', name: 'category.getAll', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager)
    {
        //dd($this->categoryService->show($entityManager));
        return $this->render('category/getAll.html.twig',[
            'categories'=>$this->categoryService->show($entityManager),
        ]);
    }


}
