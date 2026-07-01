<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CustomerController extends AbstractController
{
    private CustomerService $customerService;
    public function __construct()
    {
        $this->customerService=new CustomerService();
    }
    /**
     * Login 'get the Form'
     */
    #[Route('/customer/login', name: 'customer.login', methods:['GET'])]
    public function login(Request $request)
    {
        return $this->render('customer/login.html.twig', [
            'data' => $request->get('data'),
        ]);
    }
    /**
     * Login Test
     */
    #[Route('/customer/loginTest', name: 'customer.loginTest', methods: ['POST'])]
    public function loginTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        //Validation Errors
        if(!$this->customerService->loginTest($request, $entityManager, $passwordHasher, $security)['success'])
            {
                return $this->redirectToRoute('customer.login',[
                    'data'=>$this->customerService->loginTest($request, $entityManager, $passwordHasher, $security)
                ]);
            }
        //Success Logedin
        return $this->redirectToRoute("category.getAll");
    }
    /**
     * Register 'Get the Form'
     */
    #[Route('/customer/register', name: 'customer.register', methods: ['GET'])]
    public function register(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render("customer/register.html.twig",[
            "data"=>$request->get('data')
        ]);
    }
    /**
     * Register Test
     */
    #[Route('/customer/registerTest', name: 'customer.registerTest', methods: ['POST'])]
    public function registerTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer)
    {
        return $this->redirectToRoute("customer.register",[
            'data'=>$this->customerService->registerTest($request, $entityManager, $passwordHasher, $mailer)
        ]);
    }
    /**
     * Active the Account
     */
    #[Route('/customer/activeAccount/{token}', name: 'customer.activeAccount', methods: ['GET'])]
    public function activeAccount(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        $data=$this->customerService->activeAccount($request, $entityManager, $security);
        if($data['success'] == true)
            {
            return $this->redirectToRoute("category.getAll");
            }
    }
    /**
     * Logout
     */
    #[Route('/customer/logout', name: 'customer.logout', methods: ['GET'])]
    public function logout(Security $security)
    {
        //Loging Out Using Security
       $security->logout(false);
       return $this->redirectToRoute("category.getAll");
    }
    /**
     * Password Forget
     * Create new Password 'Get The Form'
     */
    #[Route('/customer/createNewPassword', name: 'customer.createNewPassword', methods: ['GET'])]
    public function createNewPassword(Request $request)
    {
        return $this->render('customer/createNewPassword.html.twig',[
            'data'=>$request->get('data')
        ]);
    }
    /**
     * Create new Password Testing
     */
    #[Route('/customer/createNewPasswordTest', name: 'customer.createNewPasswordTest', methods: ['POST'])]
    public function createNewPasswordTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher ,MailerInterface $mailer)
    {
        return $this->redirectToRoute("customer.createNewPassword",[
          "data"=>$this->customerService->createNewPasswordTest($request, $entityManager, $passwordHasher, $mailer)
        ]);
    }

    /**
     * Save the new Password
     */
    #[Route('/customer/saveNewPassword/{token}', name: 'customer.saveNewPassword', methods: ['GET'])]
    public function saveNewPassword(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        $this->customerService->saveNewPassword($request, $entityManager, $security);
        return $this->redirectToRoute("category.getAll");
    }

}

