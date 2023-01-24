<?php namespace App\Repositories;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaRepository
{
    protected $model;
    const PATH = 'media/files';
    public function __construct(Media $media)
    {
        $this->model =  new Repository($media);
    }
    public function upload($file,$destination)
    {
        $image_extention = $file->getClientOriginalExtension();
        $image_filename = rand(111111, 999999) . "." . $image_extention;
        $file->move($destination, $image_filename);
        return $image_filename;
//        $path = $request->file('avatar')->store('avatars');
//        return $path;
    }

    public function delete($name,$destination)
    {
        try {
            unlink($destination.'/'.$name);
        }catch (\Exception $exception){
            //...
        }
    }

    public function uploadGallery(array $files,$destination)
    {
        $uploads = array();
        foreach ($files as $file){
            array_push($uploads,$this->upload($file,$destination));
        }
        return $uploads;
    }

    public function deleteGallery($files,$destination)
    {
        $uploads = array();
        foreach ($files as $file){
            $this->delete($file,$destination);
        }
    }

    public function uploadToMedia($file)
    {
        if (isset($file)) {
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getClientMimeType();
            $data['file_name'] = $this->upload($file, self::PATH);
            return $data;
        }
        return response('فایل ارسال شده معتبر نیست',402);
    }

    public function deleteMedia(Media $media)
    {
        $this->delete($media->file_name,self::PATH);
        return $this->model->delete($media);
    }
}
