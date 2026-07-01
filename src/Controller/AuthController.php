<?php

namespace App\Controller;

use App\Services\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    private AuthService $authService;
    public function __construct()
    {
        $this->authService=new AuthService();
    }
    #[Route('/admin/auth/register', name: 'auth.register', methods:['GET'])]
    public function register(Request $request)
    {
        return $this->render('auth/register.html.twig', [
            'data' => $request->get('data'),
        ]);
    }
      #[Route('/admin/auth/registerTest', name: 'auth.registerTest', methods:['POST'])]
      public function registerTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
      {
        return $this->redirectToRoute('auth.register',[
            'data'=>$this->authService->registerTest($request, $entityManager, $passwordHasher),
        ]);
      }
    #[Route('/', name: 'auth.login', methods:['GET'])]
    public function login(Request $request)
    {

        return $this->render('auth/login.html.twig',[
            'data'=>$request->get('data')
        ]);
    }
    #[Route('/', name: 'auth.loginTest', methods: ['POST'])]
    public function loginTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security)
    {

        if(!$this->authService->loginTest($request, $entityManager, $passwordHasher, $security)['success'])
        {
            return $this->redirectToRoute('auth.login',[
            'data'=>$this->authService->loginTest($request, $entityManager, $passwordHasher, $security)
            ]);
        }
        $user=$this->authService->loginTest($request, $entityManager, $passwordHasher, $security)['user'];
        //dd($user->getRoles());

        if(in_array('ROLE_USER',$user->getRoles()))
        {
            return $this->redirectToRoute('home.index');
        }
        if(in_array('ROLE_COOK',$user->getRoles()))
        {
            return $this->redirectToRoute('cook.current');
        }
        if (in_array('ROLE_CUSTOMER', $user->getRoles())) {
            return $this->redirectToRoute('category.getAll');
        }

    }
}
