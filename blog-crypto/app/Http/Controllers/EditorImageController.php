<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        /*
         * hasRole() của project chỉ nhận một string,
         * nên phải kiểm tra AUTHOR và ADMIN riêng.
         */
        $canUpload = $user
            && method_exists($user, 'hasRole')
            && (
                $user->hasRole('AUTHOR')
                || $user->hasRole('ADMIN')
            );

        abort_unless(
            $canUpload,
            403,
            'Bạn không có quyền tải ảnh vào nội dung.'
        );

        $validated = $request->validate([
            'upload' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ], [
            'upload.required' =>
                'Bạn chưa chọn ảnh cần tải lên.',

            'upload.image' =>
                'Tệp tải lên phải là ảnh hợp lệ.',

            'upload.mimes' =>
                'Ảnh chỉ được dùng định dạng JPG, JPEG, PNG hoặc WEBP.',

            'upload.max' =>
                'Dung lượng ảnh không được vượt quá 5 MB.',

            'upload.dimensions' =>
                'Kích thước ảnh không được vượt quá 6000 x 6000 pixel.',
        ]);

        $file = $validated['upload'];

        $folder = 'editor-images/' . now()->format('Y/m');

        $path = $file->store(
            $folder,
            'public'
        );

        return response()->json([
            'url' => Storage::url($path),
        ], 201);
    }
}