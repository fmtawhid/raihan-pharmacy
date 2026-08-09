<?php

namespace  App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{

    public function media()
    {
        $pageTitle = 'Product Images';
        $mediaFiles = Media::orderBy('id', 'desc')
            ->withCount('products')
            ->withCount('productImages')
            ->withCount('productVariants')
            ->withCount('productVariantImages')
            ->paginate(48);
        return view('admin.media_uploader', compact('pageTitle', 'mediaFiles'));
    }

    public function mediaFiles()
    {
        $mediaFiles = Media::orderBy('id', 'desc')->paginate(33);
        return response()->json($mediaFiles);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photos'           => 'required|array|max:20',
            'photos.*'         => [
                'required',
                'image',
                new FileTypeValidate(['jpeg', 'jpg', 'png', 'webp']),
                'dimensions:min_width=500,min_height=500', // ✅ enforce 500x500
            ],
            'files_for'        => 'required|in:product,category,categoryIcon,brand'
        ], [
            'photos.required' => 'Please upload at least one image.',
            'photos.*.dimensions' => 'Each image must be at least 500×500 pixels.',
        ]);

        if ($validator->fails()) {
            return errorResponse($validator->errors());
        }

        $uploaded = [];
        $filesFor = $request->files_for;

        foreach ($request->photos as $photo) {
            $media = new Media();
            $media->path = getFilePath($filesFor);
            $media->file_name = fileUploader($photo, getFilePath($filesFor), getFileSize($filesFor), null, getThumbSize($filesFor));
            $media->save();
            $uploaded[] = $media;
        }

        return successResponse('Uploaded successfully', ['uploaded' => $uploaded]);
    }


    function delete($id)
    {

        try {
            $media = Media::find($id);
            fileManager()->removeFile($media->path . '/' . @$media->file_name);
            fileManager()->removeFile($media->path . '/thumb_' . @$media->file_name);
            $media->delete();
        } catch (\Exception $e) {
            $notify[] = ['error', 'File not found'];
            return back()->withNotify($notify);
        }

        $notify[] = ['success', 'Deleted successfully'];
        return back()->withNotify($notify);
    }
}
