<?php

namespace App\Controller;

use App\Services\WerbungService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class WerbungController extends AbstractController
{
    private WerbungService $werbungService;
    public function __construct()
    {
        $this->werbungService=new WerbungService();
    }
    #[Route('/user/werbung/create', name: 'werbung.create', methods:['GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('werbung/create.html.twig', [
            'screens'=>$this->werbungService->create($entityManager),
            'data' => $request->get('data'),
        ]);
    }
    #[Route('/user/werbung/save', name: 'werbung.save', methods:['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager)
    {
        $screenId=4;
        //dd($request->get("screen$screenId"));
        $storage = $this->getParameter("kernel.project_dir") . "/public/file";
        return $this->redirectToRoute("werbung.create",[
            "data"=>$this->werbungService->save($request, $entityManager, $storage)
        ]);
    }
    #[Route('/user/werbung/show', name: 'werbung.show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, Request $request)
    {
        return $this->render('werbung/show.html.twig',[
            'werbungen'=>$this->werbungService->show($entityManager),
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/user/werbung/edit/{id}', name: 'werbung.edit', methods: ['GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {

        return $this->render("werbung/edit.html.twig",[
            'werbung'=>$this->werbungService->edit($request, $entityManager)['werbung'],
            'screens'=>$this->werbungService->edit($request, $entityManager)['screens'],
        ]);
    }
    #[Route('/user/werbung/update', name: 'werbung.update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager)
    {
         $storage = $this->getParameter("kernel.project_dir") . "/public/file";
        return $this->redirectToRoute("werbung.edit",[
            'data'=>$this->werbungService->update($request, $entityManager, $storage),
            'id'=>$request->get('id')
        ]);
    }
    #[Route('/user/werbung/delete/{id}', name: 'werbung.delete', methods: ['GET'])]
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('werbung/delete.html.twig',$this->werbungService->delete($request,$entityManager));
    }
    #[Route('/user/werbung/remove', name: 'werbung.remove', methods: ['POST'])]
    public function remove(Request $request, EntityManagerInterface $entityManager)
    {
        $storage=$this->getParameter("kernel.project_dir") . "/file";
        return $this->redirectToRoute("werbung.show",[
            'data'=>$this->werbungService->remove($request, $entityManager, $storage)
        ]);
    }




}
