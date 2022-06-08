<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditorRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\IndexResource;
use App\Models\Image;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function update()
    {
        return view('update');
    }

    public function store(IndexRequest $request)
    {
        $data = $request->validated();
        $images = $data['images'];
        unset($data['images']);

        $post = Post::firstOrCreate($data);

        foreach ($images as $image) {
            $name = md5(Carbon::now().'_'.$image->getClientOriginalName()).'.'.$image->getClientOriginalExtension();
            $filePath = Storage::disk('public')->putFileAs('/images', $image, $name);
            $previewName = 'prev_'.$name;

            Image::create([
               'path' => $filePath,
               'url' => url('/storage/'.$filePath),
               'preview_url' => url('/storage/images/'.$previewName),
               'post_id' => $post->id
            ]);

            \Intervention\Image\Facades\Image::make($image)->fit(100,100)
                ->save(storage_path('app/public/images/'.$previewName));
        }
        return response()->json(['message' => 'success']);
    }

    public function getImage()
    {
      $post = Post::latest()->first();
      return new IndexResource($post);
    }

    public function imageEditor(EditorRequest $request)
    {
        $data = $request->validated();
        $image = $data['image'];

        $name = md5(Carbon::now() . '_' . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
        $filePath = Storage::disk('public')->putFileAs('/images/editor', $image, $name);


        return response()->json(['url' => url('/storage/'.$filePath)]);
    }

    public function imageUpdate(UpdateRequest $request, Post $post)
    {
        $data = $request->validated();

        $images = isset($data['images']) ? $data['images'] : null;
        $imageIdsForDelete = isset($data['image_ids_for_delete']) ? $data['image_ids_for_delete'] : null;
        $imageUrlsForDelete = isset($data['image_urls_for_delete']) ? $data['image_urls_for_delete'] : null;

        unset($data['images'], $data['image_ids_for_delete'], $data['image_urls_for_delete']);

        $post->update($data);

        $currImages = $post->images;

        if($imageIdsForDelete){
            foreach ($currImages as $currImage){
                if(in_array($currImage->id, $imageIdsForDelete)){
                    Storage::disk('public')->delete($currImage->path);
                    Storage::disk('public')->delete(str_replace('images/','images/prev_', $currImage->path));
                    $currImage->delete();
                }
            }
        }

        if ($imageUrlsForDelete) {
            foreach ($imageUrlsForDelete as $imageUrlForDelete) {
                $removeStr = $request->root() . '/storage/';
                $path = str_replace($removeStr, '', $imageUrlForDelete);
                 Storage::disk('public')->delete($path);
            }
        }

        if($images) {
            foreach ($images as $image) {
                $name = md5(Carbon::now() . '_' . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
                $filePath = Storage::disk('public')->putFileAs('/images', $image, $name);
                $previewName = 'prev_' . $name;

                Image::create([
                    'path' => $filePath,
                    'url' => url('/storage/' . $filePath),
                    'preview_url' => url('/storage/images/' . $previewName),
                    'post_id' => $post->id
                ]);

                \Intervention\Image\Facades\Image::make($image)->fit(100, 100)
                    ->save(storage_path('app/public/images/' . $previewName));
            }
        }

        return response()->json(['message' => 'success']);
    }

}
