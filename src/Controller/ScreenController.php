<?php

namespace App\Controller;

use App\Services\ScreenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ScreenController extends AbstractController
{
    private ScreenService $screenService;
    public function __construct()
    {
        $this->screenService= new ScreenService();
    }
    #[Route('/admin/screen/create', name: 'screen.create', methods:['GET'])]
    public function create( Request $request)
    {
        return $this->render('screen/create.html.twig', [
            'data' => $request->get('data'),
        ]);
    }
    #[Route('/admin/screen/save', name: 'screen.save', methods:['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager)
    {
       return $this->redirectToRoute('screen.create',[
            'data'=> $this->screenService->save($request, $entityManager)
        ]);
    }
    #[Route('/admin/screen/show', name: 'screen.show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager)
    {
        return $this->render('screen/show.html.twig',[
            'screens'=>$this->screenService->show($entityManager)
        ]);
    }
    #[Route('/admin/screen/edit/{id}', name: 'screen.edit', methods: ['GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('screen/edit.html.twig',[
            'screen'=>$this->screenService->edit($entityManager, $request->get('id')),
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/admin/screen/update', name: 'screen.update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->redirectToRoute('screen.edit',[
            'id'=>$request->get('id'),
            'data'=>$this->screenService->update($entityManager, $request)
        ]);
    }
    #[Route('/admin/screen/delete/{id}', name: 'screen.delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('screen/delete.html.twig',[
           'screen'=>$this->screenService->delete($request, $entityManager),
           'data'=>$request->get('data')
        ]);
    }
    #[Route('/admin/screen/remove', name: 'screen.remove', methods: ['POST'])]
    public function remove(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->redirectToRoute("screen.delete",[
            'data'=>$this->screenService->remove($request, $entityManager),
            'id'=>$request->get('id')
        ]);
    }


    #[Route('/user/screen/createArticleInScreen/{screenTitle}', name: 'screen.createArticleInScreen', methods: ['GET'])]
    public function createArticleInScreen (Request $request ,EntityManagerInterface $entityManager)
    {
        //dd($this->screenService->createArticleInScreen($request, $entityManager));
        return $this->render('screen/createArticleInScreen.html.twig',[
            'categories'=>$this->screenService->createArticleInScreen($request, $entityManager)['categories'],
            'screen'=>$this->screenService->createArticleInScreen($request, $entityManager)['screen'],
            'articlesScreen'=>$this->screenService->createArticleInScreen($request, $entityManager)['articlesScreen'],
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/user/screen/saveArticleInScreen', name: 'screen.saveArticleInScreen', methods: ['POST'])]
    public function saveArticleInScreen(Request $request, EntityManagerInterface $entityManager)
    {
       return $this->redirectToRoute('screen.createArticleInScreen',[
        'data'=>$this->screenService->saveArticleInScreen($request, $entityManager),
        'screenTitle'=>$request->get('screenTitle')
       ]);
    }
    /**
     * Screen vists this Controller to view the Contents
     */
    #[Route('/screen/view/{screenTitle}', name: 'screen.view', methods: ['GET'])]
    public function view(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('screen/view.html.twig',[
            'data'=>$this->screenService->view($request, $entityManager),
        ]);
    }
    /**
     * This Controller will return  a Json-Data,
     * like a Response of the Ajax-Request
     */
    #[Route('/screen/visit', name: 'screen.visit', methods: ['POST'])]
    public function visit(Request $request, EntityManagerInterface $entityManager)
    {
        //dd($this->screenService->visit($request, $entityManager));
        return $this->json([
            'categories'=>$this->screenService->visit($request, $entityManager)['categories'],
            'werbungen'=>$this->screenService->visit($request, $entityManager)['werbungen']
        ]);
    }






}
