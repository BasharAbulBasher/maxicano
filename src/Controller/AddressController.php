<?php

namespace App\Controller;

use App\Services\AddressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AddressController extends AbstractController
{
    #[Route('/customerrole/address/create', name: 'address.create', methods:['get'])]
    public function create(AddressService $addressService)
    {
        //dd($addressService->create($this->getUser()),);
        return $this->render('address/create.html.twig', [
            'addresses' => $addressService->create($this->getUser()),
        ]);
    }
    #[Route('/customerrole/address/save', name: 'address.save', methods:['POST'])]
    public function save(AddressService $addressService, Request $request)
    {
        return $this->json([
            'data'=>$addressService->save($request, $this->getUser()),
            'addresses' => $addressService->create($this->getUser()),
        ]);
    }

}
