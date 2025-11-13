<?php
// ==========================================
// app/Http/Controllers/Api/UploadController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload file (profile/car photos)
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
            'type' => 'required|in:profile,car,document,id_card',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $type = $request->type;
        
        // Generate unique filename
        $filename = $type . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs("uploads/{$type}", $filename, 'public');
        
        $url = Storage::url($path);

        return response()->json([
            'message' => 'File uploaded successfully',
            'url' => $url,
            'filename' => $filename,
            'path' => $path,
        ], 200);
    }
}