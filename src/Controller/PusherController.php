<?php

namespace App\Controller;

use App\Services\PusherService;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PusherController extends AbstractController
{
    private PusherService $pusherService;
    public function __construct()
    {
        $this->pusherService=new PusherService();
    }
    #[Route('/pusher/push', name:'pusher.push', methods:['POST'])]
    public function push(Request $request, EntityManagerInterface $entityManager, Pusher $pusher)
    {
        if($request->get('channel')=='screen')
        {
            $pusher->trigger('screen','event',[
                'tv1'           =>      $this->pusherService->push($entityManager)['tv1'],
                'tv2'           =>      $this->pusherService->push($entityManager)['tv2'],
                'tv3'           =>      $this->pusherService->push($entityManager)['tv3'],
                'tv1Werbungen'  =>      $this->pusherService->push($entityManager)['tv1Werbungen'],
                'tv2Werbungen'  =>      $this->pusherService->push($entityManager)['tv2Werbungen'],
                'tv3Werbungen'  =>      $this->pusherService->push($entityManager)['tv3Werbungen'],

            ]);
        }
        return $this->json([
            'message'=>'success pushed'
        ]);
    }

}
