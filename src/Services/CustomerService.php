<?php
namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerService
{
    /**
     * Login Test
     */
    public function loginTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        //Get Errors of Login-Validation
        $errors=$this->validationLogin($request, $entityManager, $passwordHasher);
        //Validation Errors
        if(!empty($errors))
            {
                return [
                    'success'=>false,
                    'message'=>'Validation Error',
                    'errors'=>$errors
                ];
            }
        //Find the User By Email
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        //Login the User
        $security->login($user);
        return [
            'success'=>true,
            'message'=>'Susccess Logedin'
        ];
    }
    /**
     * Register Test
     */
    public function registerTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer)
    {
        //Validation
        if(!empty($this->validation($request, $entityManager)))
            {
                return [
                    'success'=>false,
                    'message'=>'Register Errors, Confirm your Data Please',
                    'errors'=>$this->validation($request, $entityManager)
                ];
            }
        //Get the User
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        //If User registered with 'ROLE_CUSTOMER' yet
        if(!empty($user))
            {
                if(in_array("ROLE_CUSTOMER", $user->getRoles()))
                    {
                        //Send Email
                        $this->sendEmail($user, $mailer);
                        return [
                        'success' => true,
                        'message' => 'You registered with this Email yet, Please aktive your Account Using the sent Email!',
                        ];
                    }
            }
        //Save the User wit ROLE_CUSTOMER
        $user=new User();
        //Set Email
        $user->setEmail($request->get('email'));
        //Hash & Set the Password
        $user->setPassword($passwordHasher->hashPassword($user, $request->get('password')));
        //Generate & Set Token
        $user->setToken(bin2hex(random_bytes(32)));
        //Set The Rolle
        $user->setRoles(['ROLE_CUSTOMER']);
        //Save the User
        $entityManager->persist($user);
        $entityManager->flush();
        //Send Email
        $this->sendEmail($user, $mailer);
        return [
        'success' => true,
        'message' => 'Success sent an Email, Please aktive your Account!',
        ];

    }
    /**
     * Active The Account
     */
    public function activeAccount(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        //Find the User by Token
        $user=$entityManager->getRepository(User::class)->findOneBy(['token'=>$request->get('token')]);
        //User Not Found
        if(empty($user))
            {
                dd("This Account is NOT FOUND, please try to register agin");
                return [
                    "success" => false,
                    "message" => "This AccountisNOT FOUND",
                ];
            }
        //Active the Account
        $user->setRegistered(true);
        //Save the Changing in DB
        $entityManager->persist($user);
        $entityManager->flush();
        //LogIn
        $security->login($user);
        return [
            "success"=>true,
            "message"=>"Success actived the Account"
        ];
    }
    /**
     * Create new Password
     */
    public function createNewPasswordTest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher ,MailerInterface $mailer)
    {
        //Validation
        $errors=$this->newPasswordValidation($request, $entityManager);
        if(!empty($errors))
            {
                return[
                    'success'=>false,
                    'message'=>'Validation Errors',
                    'errors'=>$errors
                ];
            }
        //Get the User By Email
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        //Generate the hashed Password
        $plaintPassword=$passwordHasher->hashPassword($user, $request->get('newPassword'));
        //Set PlaintPassword
        $user->setNewPassword($plaintPassword);
        //Save the new Password
        $entityManager->persist($user);
        $entityManager->flush();
        //Send Email to Change the Password
        $this->newPasswordEmail($user,$mailer);
        return [
            'success'=>true,
            'message'=>'Successfully sent an Email, please open the sent Email and active your new Password',
        ];
    }
    /**
     * Save the new Password
     */
    public function saveNewPassword(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        //Get the Token
        $token=$request->get('token');
        //Get User By Token
        $user=$entityManager->getRepository(User::class)->findOneBy(['token'=>$token]);
        //Get the new Password
        $newPassword=$user->getNewPassword();
        //User Not Found
        if(empty($user))
            {
                dd("this Email is Not Found");
            }
        //new Password is empty
        if(empty($newPassword))
            {
                dd("The new Password is NOT FOUND");
            }
        //Set the New Password
        $user->setPassword($newPassword);
        //Set new password in NULL
        $user->setNewPassword(null);
        //Save The new Password
        $entityManager->persist($user);
        $entityManager->flush();
        //Loggin
        $security->login($user);
    }


    /**
     * Validation the Register
     */
    private function validation(Request $request, EntityManagerInterface $entityManager)
    {
        $errors=[];
        //Password & ConfirmPassword musst be have the same Value 'identicale'
        if($request->get('password') != $request->get('confirmPassword'))
            {
                $errors['confirmPassword']='ConfirmPassword nicht gleisch';
            }
        //Get User by Email
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        if(!empty($user))
            {
                //Chick if User is registered
                $isRegistered=$user->isRegistered();
                //Chick if User is an Admin
                $isAdmin=in_array("ROLE_ADMIN", $user->getRoles());
                //Email is registerd yet
                if($isRegistered == true)
                    {
                        $errors['email']="This Acount is registered";
                    }
                //Email is for an Admin
                if($isAdmin == true)
                    {
                        $errors['email']="This Acount is for an Admin";
                    }
            }
        $password=$request->get('password');
        //longer as 7
        if(strlen($password) < 8 )
            {
                $errors['password']="Password 8 zifferen oder mehr erforderlich";
            }
        //Min 1 Big Letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Mindestens ein Großbuchstabe erforderlich';
        }
        //Min 1 small Letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Mindestens ein Kleinbuchstabe erforderlich';
        }
        //Min 1 Nummber
        if (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Mindestens eine Zahl erforderlich';
        }
        return $errors;
    }
    /**
     * Send Email to aktive the Acount
     */
    private function sendEmail(User $user, MailerInterface $mailer)
    {
        $link="http://127.0.0.1:8000/customer/activeAccount/".$user->getToken();
        $email=(new Email())
        ->from('oncels@hotmail.de')
        ->to($user->getEmail())
        ->subject('Aktivieren Sie Ihren Account')
        ->html("
            <h2> Willkommen </h2 >
            <p> Bitte aktivieren Sie Ihren Account:  </p>
            <a href = '{$link}' > Account aktivieren </a>
        ");
        $mailer->send($email);
    }

    /**
     * Validation Login
     */
    private function validationLogin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $errors=[];
        //Find the User by Email
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        //User is Empty
        if(empty($user))
            {
                $errors['email']='This Email is NOT FOUND';
            }
        //Password is wrong
        if(!empty($user))
            {
            if(!$passwordHasher->isPasswordValid($user, $request->get('password')))
                {
                    $errors['password']='This Password is Wrong';
                }
            }
        return $errors;

    }
    /**
     * Validation of new Password
     */
    private function newPasswordValidation(Request $request, EntityManagerInterface $entityManager)
    {
        $errors=[];
        //Get the User
        $user=$entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('email')]);
        //User NOT Found
        if(empty($user))
            {
                $errors['email']="This Email is NOT FOUND";
            }
        //Confirm Password Error
        if($request->get('newPassword') != $request->get('confirmNewPassword'))
            {
                $errors['confirmNewPassword'] = "Confirm please Your Password";
            }
        //User is not empty
        if(!empty($user))
            {
                //User is not registered yet
                if(!$user->isRegistered())
                    {
                        $errors['email']='This Email is not registered yet';
                    }
                //User is an Admin
                if(in_array("ROLE_ADMIN", $user->getRoles()))
                    {
                        $errors['email'] = 'This Email is for an Admin';
                    }
            }
        $newPassword=$request->get('newPassword');
        //longer as 7
        if(strlen($newPassword) < 8 )
            {
                $errors['newPassword']="Password 8 zifferen oder mehr erforderlich";
            }
        //Min 1 Big Letter
        if (!preg_match('/[A-Z]/', $newPassword)) {
            $errors['newPassword'] = 'Mindestens ein Großbuchstabe erforderlich';
        }
        //Min 1 small Letter
        if (!preg_match('/[a-z]/', $newPassword)) {
            $errors['newPassword'] = 'Mindestens ein Kleinbuchstabe erforderlich';
        }
        //Min 1 Nummber
        if (!preg_match('/[0-9]/', $newPassword)) {
            $errors['newPassword'] = 'Mindestens eine Zahl erforderlich';
        }
        return $errors;
    }
    /**
     * Send Email to CHANGE the Password
     */
    private function newPasswordEmail(User $user, MailerInterface $mailer)
    {
        //Generate the Link to save the new Password
        $link = "http://127.0.0.1:8000/customer/saveNewPassword/" . $user->getToken();
        //Generate the Email
        $email=(new Email())
        ->from("oncels@hotmail.com")
        ->to($user->getEmail())
        ->subject("Active your new Password")
        ->html("
           <h2> Willkommen </h2>
            <p>Please vist the Link to active your new Password: </p>
            <a href = '{$link}' >New Password Active</a>
        ");
        $mailer->send($email);
    }

}
