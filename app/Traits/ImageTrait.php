<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Trait ImageTrait
 * 
 * This trait provides methods for handling image uploads and retrieval
 * for both admin and user sections of the application.
 */
trait ImageTrait
{
    /**
     * Upload an image to the specified directory
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @param  string  $directory
     * @param  int|null  $width
     * @param  int|null  $height
     * @return string|null
     */
    public function uploadImage($image, string $directory): ?string
    {
        if (!$image || !$image->isValid()) {
            return null;
        }

        // Generate a unique filename
        $filename = Str::random(20) . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path = 'public/' . trim($directory, '/') . '/' . $filename;

    
            // Store the original image
        $image->storeAs('public/' . trim($directory, '/'), $filename);
    

        return $filename;
    }

    /**
     * Get the full URL of an image
     *
     * @param  string|null  $filename
     * @param  string  $directory
     * @param  string  $default
     * @return string
     */
    public function getImageUrl(?string $filename, string $directory, string $default = 'default.png'): string
    {
        if (!$filename) {
            return asset('storage/' . $default);
        }

        $path = 'public/' . trim($directory, '/') . '/' . $filename;
        
        return Storage::exists($path) 
            ? asset(Storage::url($path))
            : asset('storage/' . $default);
    }

    /**
     * Delete an image from storage
     *
     * @param  string  $filename
     * @param  string  $directory
     * @return bool
     */
    public function deleteImage(string $filename, string $directory): bool
    {
        $path = 'public/' . trim($directory, '/') . '/' . $filename;
        
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        
        return false;
    }

    /**
     * Handle image update (delete old and upload new)
     *
     * @param  \Illuminate\Http\UploadedFile  $newImage
     * @param  string|null  $oldFilename
     * @param  string  $directory
     * @param  int|null  $width
     * @param  int|null  $height
     * @return string|null
     */
    public function updateImage($newImage, ?string $oldFilename, string $directory, ?int $width = null, ?int $height = null): ?string
    {
        // Delete old image if exists
        if ($oldFilename) {
            $this->deleteImage($oldFilename, $directory);
        }

        // Upload new image
        return $this->uploadImage($newImage, $directory, $width, $height);
    }
}
