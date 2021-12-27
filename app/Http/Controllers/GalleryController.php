<?php

namespace App\Http\Controllers;

use App\GalleryItem;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Storage;
use Intervention;
use JWTAuth;
use File;

class GalleryController extends Controller
{
    private $imageLocation = 'uploads/posts/';
    private $videoLocation = 'uploads/posts/';
    protected $user;

    public function __construct()
    {
        // die('constructor');
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {

        }
    }

    public function upload(Request $request)
    {
        // die('123');
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'files' => 'required',
        ]);

        $uploadedFiles = $request->file('files');
        $response = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFileMimeType = mime_content_type($uploadedFile->getPathname());

            $allowedImageMimeTypes = ['image/jpeg', 'image/png', 'image/bmp'];
            $allowedVideoMimeTypes = [];

            if (in_array($uploadedFileMimeType, $allowedImageMimeTypes)) {
                $image = Intervention::make($uploadedFile);
                $fileName = str_random(32) . '.jpg';
                $fileNamePreview = str_random(32) . '.jpg';
                list($width, $height, $type, $attr) = getimagesize($uploadedFile);


                $image->encode('jpg', 100)
                    ->backup();

                $imagePreview = $image->fit(800, 450, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }, 'top');

                Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $fileNamePreview, (string)$imagePreview->stream());

                $imageOriginal = $image->reset();

                Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $fileName, (string)$imageOriginal->stream());

                $imageWebp = Intervention::make($uploadedFile);
                $fileNameWebp = str_random(32) . '.webp';

                $imageWebp->encode('webp', 100)
                    ->backup();

                Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $fileNameWebp, (string)$imageWebp->stream());

                $responseFileName = $fileNamePreview;
                if ($imageOriginal->width() == $imageOriginal->height() && $imageOriginal->width() >= 900) {
                    $responseFileName = $fileName;
                }

                $galleryItem = new GalleryItem([
                    'filename' => $fileName,
                    'filename_webp' => $fileNameWebp,
                    'image_x' => $width,
                    'image_y' => $height,
                    'filename_preview' => $responseFileName,
                    'type' => 'image',
                    'title' => $request->get('title', null),
                ]);

                $galleryItem->save();

                $response[] = $galleryItem;
            } elseif (in_array($uploadedFileMimeType, $allowedVideoMimeTypes)) {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => config('ffmpeg.ffmpeg_binary'),
                    'ffprobe.binaries' => config('ffmpeg.ffprobe_binary')
                ]);
                $ffprobe = FFProbe::create([
                    'ffmpeg.binaries' => config('ffmpeg.ffmpeg_binary'),
                    'ffprobe.binaries' => config('ffmpeg.ffprobe_binary')
                ]);

                $fileName = str_random(32);
                $previewFileName = str_random(32) . '.jpg';
                $video = $ffmpeg->open($uploadedFile->getPathname());
                $duration = $ffprobe->streams($uploadedFile->getPathname())
                    ->videos()
                    ->first()
                    ->get('duration');

                $video->frame(TimeCode::fromSeconds(floor($duration / 2)))
                    ->save(storage_path() . '/tmp/video_frames/' . $previewFileName);

                $image = Intervention::make(storage_path() . '/tmp/video_frames/' . $previewFileName)
                    ->interlace(true)
                    ->encode('jpg', 100)
                    ->fit(800, 450, function ($constraint) {
                        $constraint->upsize();
                    })
                    ->stream();
                list($width, $height, $type, $attr) = getimagesize($uploadedFile);

                File::delete(storage_path() . '/tmp/video_frames/' . $previewFileName);

                Storage::disk(config('app.gallery_disk'))->put($this->imageLocation . $previewFileName, (string)$image, 'public');

                Storage::disk(config('app.gallery_disk'))->putFileAs(
                    $this->videoLocation,
                    $uploadedFile,
                    $fileName . '.' . $uploadedFile->getClientOriginalExtension(),
                    'public'
                );

                $galleryItem = new GalleryItem([
                    'filename' => $fileName . '.' . $uploadedFile->getClientOriginalExtension(),
                    'filename_webp' => $fileName . '.' . $uploadedFile->getClientOriginalExtension(),
                    'image_x' => $width,
                    'image_y' => $height,
                    'filename_preview' => $previewFileName,
                    'type' => 'video',
                    'title' => $request->get('title'),
                    'show_in_video_gallery' => (bool)$request->get('show_in_video_gallery'),
                ]);

                $galleryItem->save();
                $galleryItem->videoGalleryCategories()->sync(json_decode($request->get('categories'), true));

                $response[] = $galleryItem;
            }
        }

        return $response;
    }

    public function patch($id, Request $request)
    {
        if (!$this->user) {
            abort(401);
        }

        if (!$this->user->can('articles_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $galleryItem = GalleryItem::findOrFail($id);
        $uploadedFile = $request->file('file');
        $allowedVideoMimeTypes = [];
        $data = [];

        if ($request->has('title')) {
            $data['title'] = $request->get('title');
        }

        if ($galleryItem->type != 'video') {
            return response()
                ->json([
                    'error' => 'Selected gallery item is not a video.'
                ], 422);
        }

        if (!empty($uploadedFile)) {
            $uploadedFileMimeType = mime_content_type($uploadedFile->getPathname());

            if (in_array($uploadedFileMimeType, $allowedVideoMimeTypes)) {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => config('ffmpeg.ffmpeg_binary'),
                    'ffprobe.binaries' => config('ffmpeg.ffprobe_binary')
                ]);
                $ffprobe = FFProbe::create([
                    'ffmpeg.binaries' => config('ffmpeg.ffmpeg_binary'),
                    'ffprobe.binaries' => config('ffmpeg.ffprobe_binary')
                ]);

                $fileName = str_random(32);
                $previewFileName = str_random(32) . '.jpg';
                $video = $ffmpeg->open($uploadedFile->getPathname());
                $duration = $ffprobe->streams($uploadedFile->getPathname())
                    ->videos()
                    ->first()
                    ->get('duration');

                $video->frame(TimeCode::fromSeconds(floor($duration / 2)))
                    ->save(storage_path() . '/tmp/video_frames/' . $previewFileName);

                $image = Intervention::make(storage_path() . '/tmp/video_frames/' . $previewFileName)
                    ->interlace(true)
                    ->encode('jpg', 85)
                    ->fit(800, 450, function ($constraint) {
                        $constraint->upsize();
                    })
                    ->stream();

                Storage::disk('sftp')->put($this->imageLocation . $previewFileName, (string)$image, 'public');

                Storage::disk('sftp')->putFileAs(
                    $this->videoLocation,
                    $uploadedFile,
                    $fileName . '.' . $uploadedFile->getClientOriginalExtension(),
                    'public'
                );

                $data['filename'] = $this->videoLocation . $fileName . '.' . $uploadedFile->getClientOriginalExtension();
                $data['filename_preview'] = $this->imageLocation . $previewFileName;
            } else {
                return response()
                    ->json([
                        'error' => 'Uploaded file is not a video. Supported file types: ' . implode(', ', $allowedVideoMimeTypes)
                    ], 422);
            }
        }

        $galleryItem->update($data);
        $galleryItem->videoGalleryCategories()->sync(json_decode($request->get('categories'), true));

        return GalleryItem::find($id);
    }

    public function uploadSetantaMediaVideoData(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);

        $videoData = @file_get_contents('https://api.setantamedia.ge/videos/' . $request->get('id'));

        if (!$videoData) {
            return abort(400);
        }

        $videoData = json_decode($videoData);

        $galleryItem = new GalleryItem([
            'type' => 'video',
            'filename' => $request->get('id'),
            'filename_preview' => $videoData->image
        ]);

        $galleryItem->save();

        return $galleryItem;
    }

    // private function processImage($file, $name) {  
    //     $file->move(public_path($imageLocation), $name);  

    //     $webp = public_path() + $imageLocation + $name;      
    //     $im = imagecreatefromstring(file_get_contents($webp)); 
    //     $new_webp = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $webp);  
    //     imagewebp($im, $new_webp, 50);
    // }
}
