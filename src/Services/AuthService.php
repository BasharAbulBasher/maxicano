<?php
namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService{


    public function registerTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        //Validation Email, Email musst be uniq
        if(!empty($entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')])))
        {
            return [
                'success'=>false,
                'message'=>'This Email is NOT Valid, It was registered jet..'
            ];
        }
        //Init User
        $user=new User();
        //Set Email
        $user->setEmail($request->get('email'));
        //Set hashed Password
        $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));
        //Set ROLLE of The User
        $user->setRoles([$request->get('rolle')]);
        //SQL Execute
        $entityManager->persist($user);
        $entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Successfully created...'
        ];

    }

    public function loginTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        //Check if Email exists
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        if(empty($user))
        {
            return[
                'success'=>false,
                'message'=>'Email NOT FOUND...'
            ];
        }
        //Check if Passwordn is Valid
        if(!$passwordHasher->isPasswordValid($user, $request->get('password')))
        {
            return[
                'success'=>false,
                'message'=>'The Password is wrong'
            ];
        }
        //Log in
        $security->login($user);
        return[
            'success'=>true,
            'message'=>'Successfully loged in',
            'user'=>$user,
        ];

    }

}
