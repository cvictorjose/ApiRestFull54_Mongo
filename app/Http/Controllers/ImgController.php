<?php

namespace App\Http\Controllers;

use App\Story;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ImgController extends Controller
{

   public $rules;
    /**
     * Display the story cover.
     *
     * @return \Illuminate\Http\Response
     */
    public function coverStory($id)
    {
        try {
            $result = Story::where('_id', $id)->firstOrFail();
            $cover = $result->getCoverPhoto();
            if (empty($cover) || is_null($cover)){
                /*print_r($cover);*/
                /*$file = Storage::disk('public')->get('cover_default.jpg');
                $check_file = Storage::disk('public')->exists('cover_default.jpg');
                if (isset($check_file))return response($file, 200)->header('Content-Type', 'image/jpeg');*/
                return $this->coverUser($result->user_id);
            }
            return response(base64_decode($cover['code']), 200)
                ->header('Content-Type', $cover['content']);
        } catch (Exception $e) {
            $this->insertErrorDebug('Error showing Cover photo story: '.$e);
            return $this->createMessageError($e->getMessage(),$e->getStatusCode());
        }
    }



    /**
     * Display the profile cover.
     *
     * @return \Illuminate\Http\Response
     */
    public function profileUser($id)
    {
        try {
            $result = User::withTrashed()->where('_id', $id)->firstOrFail();
            $photo = $result->getProfilePhoto();

            if (empty($photo) || is_null($photo)){
                $file = Storage::disk('public')->get('profile_default.jpg');
                $check_file = Storage::disk('public')->exists('profile_default.jpg');
                if (isset($check_file))return response($file, 200)->header('Content-Type', 'image/jpeg');
            }
           return response(base64_decode($photo['code']), 200)->header('Content-Type', $photo['content']);
        } catch (Exception $e) {
            $this->insertErrorDebug('Error showing photo profile User: '.$e);
            return $this->createMessageError($e->getMessage(),$e->getStatusCode());
        }
    }

    /**
     * Display the story cover.
     *
     * @return \Illuminate\Http\Response
     */
    public function coverUser($id)
    {
        try {
            $result = User::withTrashed()->where('_id', $id)->firstOrFail();
            $photo = $result->getCoverPhoto();
            if (empty($photo) || is_null($photo)){
                $file = Storage::disk('public')->get('cover_default.jpg');
                 $check_file = Storage::disk('public')->exists('cover_default.jpg');
                 if (isset($check_file)) return response($file, 200)->header('Content-Type', 'image/jpeg');
            }
            return response(base64_decode($photo['code']), 200)->header('Content-Type', $photo['content']);
        } catch (Exception $e) {
            $this->insertErrorDebug('Error showing photo profile User: '.$e);
            return $this->createMessageError($e->getMessage(),$e->getStatusCode());
        }
    }



    /**
     * Get Video, Image or Doc from home/user/media
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */

    public function getShareFile($type, $id)
    {

        switch($type)
        {
            case 'videos':
            {
                $folders=["preload_vid","media_vid"];

            }
                break;

            case 'images':
            {

                $folders=["preload_img","media_img"];

            }
                break;

            case 'docs':
            {
                $folders=["preload_doc","media_doc"];

            }
                break;

            default: abort(404, 'NOT_FOUND');
        }

        foreach ($folders as $f)
        {
            $file = Storage::disk($f)->exists($id);

            if($file){
                $file = Storage::disk($f)->get($id);
                $ext = explode('.',$id);
                return response($file, 200)->header('Content-Type', "$type/$ext[1]");
            }
        }
        abort(400, 'BAD_REQUEST');
    }




    /**
     * Upload Video, Image or Doc to home/user/media
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */
    public function UpFile(Request $request, $type)
    {
        $file = $request->file('file');
        if($file)
        {
            switch($type)
            {
                case 'videos':
                {
                    $rules = ['file' => 'required|mimes:mp4,avi,asf,mov,qt,avchd,flv,swf,mpg,mpeg,mpeg-4,wmv,divx,3gp'];
                }
                    break;

                case 'images':
                {
                    $rules = ['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg'];
                }
                    break;

                case 'docs':
                {
                    $rules = ['file' => 'required|mimes:pdf'];
                }
                    break;

                default: abort(404, 'NOT_FOUND');
            }

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails())
            {
                return $this->createMessageError($validator->errors()->all(),"400");
            }

            $result=Storage::disk('preload')->put($type,  $file);
            $split = explode('/',$result);
            return response()->json(['link' => env('APP_URL').'/media/'.$type.'/'.$split[1]], 200);
        }
        return response()->json(['error' => trans('error.BAD_REQUEST'), 'code' => 400], 400);
    }



}
