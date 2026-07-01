<?php
namespace App\Services;
 
use App\Entity\Screen;
use App\Entity\ScreenWerbung;
use App\Entity\Werbung;
use App\Helper\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class WerbungService
{

    public function create(EntityManagerInterface $entityManager)
    {
        return $entityManager->getRepository(Screen::class)->findAll();
    }
    public function save(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        $werbung= new Werbung();
        //Validation
        if(!empty($this->validation($request->files->get('image'), $request->files->get('video'), $request, $entityManager, $werbung)))
        {
            return[
                'success'=>false,
                'message'=>'Validation Error',
                'err'=>$this->validation($request->files->get('image'), $request->files->get('video'), $request, $entityManager, $werbung)
            ];
        }
        //Setter
        $werbung=$this->setter($request, $werbung, $storage);
        //Save the Werbung
        $entityManager->persist($werbung);
        $entityManager->flush();
        //Sett Screens
         $this->setScreens($request, $entityManager, $werbung);
        return[
            'success'=>true,
            'message'=>"Successfully created new 'Werbung'",
            ];

    }

    public function show(EntityManagerInterface $entityManager)
    {
        $werbungen=[];
        foreach($entityManager->getRepository(Werbung::class)->findAll() as $werbung)
            {
                $werbungenScreens=$entityManager->getRepository(ScreenWerbung::class)->findBy(['werbung'=>$werbung->getId()]);
                $screen_arr=[];
                foreach($werbungenScreens as $werbungScreen)
                {
                    array_push($screen_arr,$werbungScreen->getScreen()->toArray());
                }
                $werbung_arr=[
                    'id' => $werbung->getId(),
                    'nr' => $werbung->getNr(),
                    'title' => $werbung->getTitle(),
                    'description' => $werbung->getDescription(),
                    'price' => $werbung->getPrice(),
                    'image' => $werbung->getImage(),
                    'video' => $werbung->getVideo(),
                    'length'=>$werbung->getLength(),
                    'screens'=>$screen_arr
                ];
                array_push($werbungen,$werbung_arr);
            }

        return $werbungen;
    }
    public function edit(Request $request, EntityManagerInterface $entityManager)
    {
        //Get Werbung by Id
        $werbung=$entityManager->getRepository(Werbung::class)->find($request->get('id'));
        //Get ScreensWerbungen
        $screensWerbungen=$entityManager->getRepository(ScreenWerbung::class)->findBy(['werbung'=>$werbung]);
        $screen_arr=[];
        foreach($screensWerbungen as $werbungScreen)
            {
                array_push($screen_arr,$werbungScreen->getScreen()->toArray());
            }
        $werbung_arr = [
        'id'          => $werbung->getId(),
        'nr'          => $werbung->getNr(),
        'title'       => $werbung->getTitle(),
        'description' => $werbung->getDescription(),
        'price'       => $werbung->getPrice(),
        'image'       => $werbung->getImage(),
        'video'       => $werbung->getVideo(),
        'length'      => $werbung->getLength(),
        'screens'     => $screen_arr,
        ];
        return [
            'werbung'=>$werbung_arr,
            'screens'=>$entityManager->getRepository(Screen::class)->findAll()
        ];
    }
    public function update(Request $request, EntityManagerInterface $entityManager, $storage)
    {
        //Get Werbung
        $werbung=$entityManager->getRepository(Werbung::class)->find($request->get('id'));
        //Validation
        if(!empty($this->validation($request->files->get('image'), $request->files->get('video'), $request, $entityManager, $werbung)))
        {
            return [
                'success'=>false,
                'message'=>'Validation Error',
                'err'=>$this->validation($request->files->get('image'), $request->files->get('video'), $request, $entityManager, $werbung)
            ];
        }
        //Setter
        $werbung=$this->setter($request, $werbung, $storage);
        //Remove the Old Screens
        $this->removeScreens($entityManager, $werbung);
        //Sett the New Screens
         $this->setScreens($request, $entityManager, $werbung);
        return[
            'success'=>true,
            'message'=>"Successfully updated new 'Werbung'",
        ];

    }
    public function delete(Request $request, EntityManagerInterface $entityManager)
    {
        $werbung=$entityManager->getRepository(Werbung::class)->find($request->get('id'));
        if(empty($werbung))
        {
            return[
                'success'=>false,
                'message'=>"This 'Werbung' is NOT FOUND"
            ];
        }
        return[
            'success'=>true,
            'message'=>"Wolud you like to delete this 'Werbung' Nr.".$werbung->getNr(),
            'werbung'=>$werbung,
        ];
    }

    public function remove(Request $request, EntityManagerInterface $entityManager,$storage)
    {
        $werbung=$entityManager->getRepository(Werbung::class)->find($request->get('id'));
        if(!empty($werbung))
        {
            //Remove the Image
            if(!empty($werbung->getImage()))
                $this->removeFile($storage, $werbung->getImage());
            //Remove the Video
            if(!empty($werbung->getVideo()))
            {
                $this->removeFile($storage, $werbung->getVideo());
            }
            $entityManager->remove($werbung);
            $entityManager->flush();
            return[
                'success'=>true,
                'message'=>"Successfully deleted the 'Werbung'",
            ];
        }
        return[
            'success'=>false,
            'message'=>"The Werbung is NOT FOUND"
        ];
    }


    //Validation
    private function validation(?UploadedFile $image, ?UploadedFile $video, Request $request,EntityManagerInterface $entityManager, Werbung $werbung)
    {
        $err=[];
        //Valid image
        if(!empty($image))
        {
            if(!getimagesize($image))
                {
                    $err['image']="The Image must be .jpg- or .npg- Detei";
                }
        }
        //Valid Video
        if(!empty($video))
        {
            //Generate an Arry Using Seperatorer '.'
            $arr = explode(".", $request->files->get('video')->getClientOriginalName());
            //Get the Last Element in the Array
            $end = $arr[count($arr) - 1];
            if($end != "mp4")
                {
                    $err['video']="The Video Must be '.mp4' Datei";
                }
        }
        //Valid Nr Case Create
        if(empty($werbung->getId()))
        {
            if(!empty($entityManager->getRepository(Werbung::class)->findOneBy(['nr'=>$request->get('nr')])))
            {
                $err['nr']="This Nr. is not Valid. Another 'werbung' has this Number";
            }
        }
        //Valid Nr Case Update
        if(!empty($werbung->getId()))
        {
            //Get a 'Werbung' using the requested Number
            $another_werbung=$entityManager->getRepository(Werbung::class)->findOneBy(['nr'=>$request->get('nr')]);
            //Case Not empty
            if(!empty($another_werbung))
            {
                if($werbung->getId() !=$another_werbung->getId())
                {
                    $err['nr'] = "This Nr. is not Valid. Another 'werbung' has this Number";
                }
            }

        }
        //Valid Screen 'Minimum One Screen must be active'
         $screen_exist=false;
        foreach($entityManager->getRepository(Screen::class)->findAll() as $screen)
            {
                $screenId=$screen->getId();
                if(!empty($request->get("screen" . $screenId)))
                    {
                        $screen_exist=true;
                    }
            }
        if(empty($screen_exist))
            {
                $err['screen']="Please Identify the Screen, which shows this 'Werbung'";
            }
        return $err;
    }
    //Setter
    private function setter(Request $request, Werbung $werbung, $storage)
    {
        //sett Nr
        $werbung->setNr($request->get('nr'));
        //sett Title
        if(!empty($request->get('title')))
            {
                $werbung->setTitle($request->get('title'));
            }
        //Sett Description
        if(!empty($request->get('description')))
            {
                $werbung->setDescription($request->get('description'));
            }
        //Sett Price
        if(!empty($request->get('price')))
            {
                $werbung->setPrice($request->get('price'));
            }
        //Set Image
        if(!empty($request->files->get('image')))
            {
                //Case Create
                if(empty($werbung->getId()))
                {
                    //Set Image
                    $werbung->setImage($this->saveFile($request->files->get('image'), $storage));
                }
                //Case Update
                if(!empty($werbung->getId()))
                {
                    //Remove Old Image
                    $this->removeFile($storage, $werbung->getImage());
                    //Set Image
                    $werbung->setImage($this->saveFile($request->files->get('image'), $storage));
                }
            }
        //Set Video
        if(!empty($request->files->get('video')))
            {
                //Case Create
                if(empty($werbung->getId()))
                {
                    //Set Image
                    $werbung->setVideo($this->saveFile($request->files->get('video'),$storage));
                }
                //Case Update
                if(!empty($werbung->getId()))
                {
                    //Remove Old Image
                    $this->removeFile($storage, $werbung->getVideo());
                    //Set Image
                    $werbung->setVideo($this->saveFile($request->files->get('video'), $storage));
                }
            }
        //Set Length of Video
        if (!empty($request->get('length'))){
            $werbung->setLength($request->get('length'));
        }
        return $werbung;
    }
    //Sett Screens
    private function setScreens(Request $request, EntityManagerInterface $entityManager, Werbung $werbung)
    {
        foreach($entityManager->getRepository(Screen::class)->findAll() as $screen)
        {
            $screenId=$screen->getId();
            if(!empty($request->get("screen".$screenId)))
                {
                    $screenWerbung=new ScreenWerbung();
                    //Set Screen
                    $screenWerbung->setScreen($entityManager->getRepository(Screen::class)->find($screenId));
                    //Set Werbung
                    $screenWerbung->setWerbung($werbung);
                    //Save ScreenWerbung
                    $entityManager->persist($screenWerbung);
                    $entityManager->flush();
                }
        }
    }
    //Remove Screens
    private function removeScreens(EntityManagerInterface $entityManager, Werbung $werbung)
    {
        foreach($entityManager->getRepository(ScreenWerbung::class)->findBy(['werbung'=>$werbung]) as $screenWerbung)
            {
                $entityManager->remove($screenWerbung);
                $entityManager->flush();
            }
    }
    //Save File
    private function saveFile(UploadedFile $image_video, $storage)
    {
        $file=new File();
        $file->setFile($image_video);
        $dir_path="werbung";
        return $file->saveFile($storage, $dir_path);
    }
    //Remove File
    private function removeFile($storage, $file_name)
    {
        $file=new File();
        $dir_path="werbung";
        $file->removeFile($storage, $dir_path, $file_name);
    }
}
