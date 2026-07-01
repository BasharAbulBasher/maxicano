<?php

namespace App\Controller;

use App\Services\HomeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private HomeService $homeService;
    public function __construct()
    {
        $this->homeService=new HomeService();
    }
    #[Route('/home/index', name: 'home.index', methods:['GET'])]
    public function index()
    {
        $user=$this->getUser();
       if(in_array("ROLE_ADMIN",$user->getRoles()))
        {
            return $this->redirectToRoute('home.admin');
        }
        if(in_array("ROLE_KELLNER",$user->getRoles()))
        {
            return $this->redirectToRoute('home.kellner');
        }

        return $this->redirectToRoute('home.user');
    }
    #[Route('/admin/home', name: 'home.admin', methods: ['GET'])]
    public function admin()
    {
        return $this->render('home/admin.html.twig');
    }
    #[Route('/user/home', name: 'home.user', methods: ['GET'])]
    public function user(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render('home/user.html.twig',[
            'categories'=>$this->homeService->user($request, $entityManager)
        ]);
    }
    #[Route('/kellner/home', name: 'home.kellner', methods: ['GET'])]
    public function kellner(EntityManagerInterface $entityManager)
    {
        //dd($this->homeService->kellner($entityManager));
        return $this->render('home/kellner.html.twig', [
            'categories' => $this->homeService->kellner( $entityManager),
        ]);
    }



}
