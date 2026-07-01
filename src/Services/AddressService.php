<?php
namespace App\Services;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AddressService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * Create 'Get Form'
     * &
     * Gett All Addresses By User
    */
    public function create(User $user)
    {
        $addresses_arr=[];
        //Get ALL Addresses by User
        $addresses=$this->entityManager->getRepository(Address::class)->findBy(['user'=>$user]);
        if(!empty($addresses))
            {
                foreach($addresses as $address)
                    {
                        array_push($addresses_arr, $address->toArray());
                    }
            }
        return $addresses_arr;
    }
    /**
     * Save new Address
     */
    public function save(Request $request, User $user)
    {
        //Validation
        $errors=$this->validation($request);
        if(!empty($errors))
            {
                return [
                'success' => false,
                'message' => 'Validation Errors',
                'errors'=>$errors,
                ];

            }
        $address=new Address();
        //Setter
        $address->setFirstName($request->get('firstName'));
        $address->setLastName($request->get('lastName'));
        $address->setStreet($request->get('street'));
        $address->setHausNr($request->get('hausNr'));
        $address->setPlz($request->get('plz'));
        $address->setCity($request->get('city'));
        $address->setUser($user);
        //Save
        $this->entityManager->persist($address);
        $this->entityManager->flush();
        return[
            'success'=>true,
            'message'=>'Success Created an new Address'
        ];
    }

    /**
    * Create Validation
    */
    private function validation(Request $request)
    {
        $errors=[];
        $essen_plz=['45138','45137','45136'];

        //Validation Firstname 'No Numbers'
        if (preg_match('/[0-9]/', $request->get('firstName'))) {
            $errors['firstName'] = 'Eine Zahl in diesem Feld ist falsch';
        }

        //Validation Lastname 'No Numbers'
        if (preg_match('/[0-9]/', $request->get('lastName'))) {
            $errors['lastName'] = 'Eine ZahlindiesemFeldistfalsch';
        }


        //Validation PLZ
        if(!in_array($request->get('plz'), $essen_plz))
            {
                $errors['plz']="Ihr PLZ ist Leider Nicht in Essen";
            }
        return $errors;
    }

}
