<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Media;
use App\Models\Player;
use App\Repositories\MediaRepository;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    protected $model;
    protected $mediaRepository;

    const PATH = 'media/files';

    public function __construct(Media $media,MediaRepository $mediaRepository)
    {
        $this->model =  new Repository($media);
        $this->mediaRepository = $mediaRepository;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request,Media $media = null)
    {
        $conditions = array();
        if ($media)
            $conditions['id'] = $media->id;

        if ($request->conditions)
            $conditions = json_decode($request->conditions,true);
        if (isset($conditions['player_id'])){
            $conditions['model_id'] = $conditions['player_id'];
            $conditions['model_type'] = 'App\Models\Player';
            unset($conditions['player_id']);
        }
        if (isset($conditions['coach_id'])){
            $conditions['model_id'] = $conditions['coach_id'];
            $conditions['model_type'] = 'App\Models\Coach';
            unset($conditions['coach_id']);
        }
        if (isset($conditions['academy_id'])){
            $conditions['model_id'] = $conditions['academy_id'];
            $conditions['model_type'] = 'App\Models\Academy';
            unset($conditions['academy_id']);
        }
        if (isset($conditions['club_id'])){
            $conditions['model_id'] = $conditions['club_id'];
            $conditions['model_type'] = 'App\Models\Club';
            unset($conditions['club_id']);
        }
        $model = $this->model;
        if ($conditions)
            $model = $this->model->list($conditions);

        if ($request->with)
            $model = $model->with($request->with);

        if ($conditions || $request->with){
            if ($request->noPaginate){
                return $model->get();
            }
            return $model->paginate();
        }
        if ($request->noPaginate){
            return $this->model->all();
        }else{
            return $this->model->paginate();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only($this->model->getModel()->fillable);
        if (isset($request->item)) {
            $data['file_size'] = $request->file('item')->getSize();
            $data['file_type'] = $request->file('item')->getClientMimeType();
            $data['file_name'] = $this->mediaRepository->upload($request->item, self::PATH);

            if (isset($data['player_id'])) {
                $item = Player::find($data['player_id']);
                if (isset($item))
                    return $item->media()->create($data);
            }
            elseif (isset($data['coach_id'])) {
                $item = Coach::find($data['coach_id']);
                if (isset($item))
                    return $item->media()->create($data);
            }
            elseif (isset($data['club_id'])) {
                $item = Club::find($data['club_id']);
                if (isset($item))
                    return $item->media()->create($data);
            }
            elseif (isset($data['academy_id'])) {
                $item = Academy::find($data['academy_id']);
                if (isset($item))
                    return $item->media()->create($data);
            }
            return $this->model->create($data);
        }
        return response('فایل ارسال شده معتبر نیست',402);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function show(Media $media)
    {
        return $this->model->show($media);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Media $media)
    {
        return $this->model->update($request->only($this->model->getModel()->fillable), $media);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function destroy(Media $media)
    {
        return $this->model->delete($media);
    }

    public function upload(Request $request)
    {
        if (Auth::user()) {
            $user_id = Auth::user()->id;
            $media = new Media();
            $media->user_id = $user_id;
          if ($request->filepond) {
                $data = $this->mediaRepository->uploadToMedia($request->filepond);
                $media->file_size = $data['file_size'];
                $media->file_type = $data['file_type'];
                $media->file_name = $data['file_name'];
                $media->save();
                return $media;
            }
            else
                return response('فایل ارسال شده معتبر نیست', 402);
        }
        return response('شما اجازه ارسال فایل را ندارید!', 402);
    }

    public function showImage(Request $request){
        return 'api.kicket.ir/media/files/211277.jpg';
    }
    public function remove(Request $request,Media $media)
    {
        if (Auth::user()) {
            return $this->mediaRepository->deleteMedia($media);
        }
        return response('شما اجازه حذف فایل را ندارید!', 402);
    }
}
