<?php

namespace App\Http\Controllers;

use App\Services\ImagePrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImagePrintController extends Controller
{
    private $imagePrinter;
    
    public function __construct(ImagePrinterService $imagePrinter)
    {
        $this->imagePrinter = $imagePrinter;
    }
    
    /**
     * ছবি আপলোড করে প্রিন্ট করুন (Form থেকে)
     */
    public function printUploadedImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,bmp|max:5120', // 5MB max
            'width' => 'nullable|integer|min:100|max:576',
            'centered' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $maxWidth = $request->input('width', 576);
        $centered = $request->input('centered', true);
        
        $result = $this->imagePrinter->printUploadedImage(
            $request->file('image'),
            $maxWidth,
            $centered
        );
        
        return response()->json($result);
    }
    
    /**
     * Local ফাইল পাথ থেকে প্রিন্ট করুন
     */
    public function printLocalImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'width' => 'nullable|integer|min:100|max:576',
            'centered' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $imagePath = $request->input('path');
        $maxWidth = $request->input('width', 576);
        $centered = $request->input('centered', true);
        
        // Security: শুধু public folder এর ছবি allow করুন
        if (!str_starts_with(realpath($imagePath), realpath(public_path()))) {
            return response()->json([
                'success' => false,
                'message' => 'Security: শুধু public folder এর ছবি প্রিন্ট করা যাবে'
            ], 403);
        }
        
        $result = $this->imagePrinter->printImage($imagePath, $maxWidth, $centered);
        
        return response()->json($result);
    }
    
    /**
     * URL থেকে ছবি প্রিন্ট করুন
     */
    public function printImageFromUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'width' => 'nullable|integer|min:100|max:576',
            'centered' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $imageUrl = $request->input('url');
        $maxWidth = $request->input('width', 576);
        $centered = $request->input('centered', true);
        
        $result = $this->imagePrinter->printImageFromUrl($imageUrl, $maxWidth, $centered);
        
        return response()->json($result);
    }
    
    /**
     * QR Code প্রিন্ট করুন
     */
    public function printQRCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|string|max:500',
            'size' => 'nullable|integer|min:100|max:400'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->input('data');
        $size = $request->input('size', 300);
        
        $result = $this->imagePrinter->printQRCode($data, $size);
        
        return response()->json($result);
    }
    
    /**
     * একাধিক ছবি একসাথে প্রিন্ট করুন
     */
    public function printMultipleImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,bmp|max:5120',
            'width' => 'nullable|integer|min:100|max:576',
            'spacing' => 'nullable|integer|min:0|max:5'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $maxWidth = $request->input('width', 576);
        $spacing = $request->input('spacing', 2);
        
        // Save uploaded images temporarily
        $imagePaths = [];
        foreach ($request->file('images') as $file) {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('multi_') . '.' . $file->getClientOriginalExtension();
            $file->move(dirname($tempPath), basename($tempPath));
            $imagePaths[] = $tempPath;
        }
        
        // Print all images
        $result = $this->imagePrinter->printMultipleImages($imagePaths, $maxWidth, $spacing);
        
        // Clean up temp files
        foreach ($imagePaths as $path) {
            @unlink($path);
        }
        
        return response()->json($result);
    }
    
    /**
     * টেস্ট: Sample logo প্রিন্ট করুন
     */
    public function testPrintLogo()
    {
        // Check if test logo exists
        $logoPath = public_path('images/test-logo.png');
        
        if (!file_exists($logoPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Test logo not found. Upload a logo to public/images/test-logo.png'
            ]);
        }
        
        $result = $this->imagePrinter->printImage($logoPath, 300, true);
        
        return response()->json($result);
    }
    
    /**
     * Image Upload Form দেখান (HTML)
     */
    public function showUploadForm()
    {
        return view('image-upload-form');
    }
}