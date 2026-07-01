<?php
namespace App\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class File
{
    private UploadedFile $file;

    //set  File
    public function setFile(UploadedFile $file)
    {
        $this->file=$file;
    }
    //get File
    public function getFile()
    {
        return $this->file;
    }
    public function saveFile(string $storage, string $dir_path)
    {
        //form complete Path
        $complete_path=$storage."/".$dir_path;
        //form fileName
        $file_name=time().$this->file->getClientOriginalName();
        //save the File
        $this->file->move($complete_path,$file_name);
        return $file_name;

    }
    //Delete the File
    public function removeFile(string $storage, string $path, string $file_name)
    {
        //Generate the Path to File
        $complete_path=$storage."/".$path."/".$file_name;
        if(file_exists($complete_path))
        {
            unlink($complete_path);
        }
    }



}
