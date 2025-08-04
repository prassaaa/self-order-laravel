<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and process an image.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $sizes
     * @return string
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images', array $sizes = []): string
    {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        // Create image instance
        $image = $this->imageManager->read($file->getPathname());

        // Default sizes if none provided
        if (empty($sizes)) {
            $sizes = [
                'original' => null,
                'large' => [800, 600],
                'medium' => [400, 300],
                'thumbnail' => [150, 150],
            ];
        }

        // Process and save different sizes
        foreach ($sizes as $sizeName => $dimensions) {
            $processedImage = clone $image;
            
            if ($dimensions) {
                [$width, $height] = $dimensions;
                $processedImage->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save to storage
            $sizePath = $sizeName === 'original' ? $path : $directory . '/' . $sizeName . '_' . $filename;
            Storage::disk('public')->put($sizePath, $processedImage->encode());
        }

        return $path;
    }

    /**
     * Delete an image and all its variants.
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage(string $path): bool
    {
        if (!$path) {
            return false;
        }

        // Delete original image
        Storage::disk('public')->delete($path);

        // Delete variants
        $pathInfo = pathinfo($path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];

        $variants = ['large_', 'medium_', 'thumbnail_'];
        foreach ($variants as $variant) {
            $variantPath = $directory . '/' . $variant . $filename;
            Storage::disk('public')->delete($variantPath);
        }

        return true;
    }

    /**
     * Get image URL with size variant.
     *
     * @param string|null $path
     * @param string $size
     * @return string|null
     */
    public function getImageUrl(?string $path, string $size = 'original'): ?string
    {
        if (!$path) {
            return null;
        }

        if ($size === 'original') {
            return asset('storage/' . $path);
        }

        $pathInfo = pathinfo($path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];
        $variantPath = $directory . '/' . $size . '_' . $filename;

        return asset('storage/' . $variantPath);
    }

    /**
     * Validate image file.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = 'File must be an image (JPEG, PNG, GIF, or WebP).';
        }

        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size must not exceed 5MB.';
        }

        // Check image dimensions
        if ($file->getMimeType() && str_starts_with($file->getMimeType(), 'image/')) {
            $imageSize = getimagesize($file->getPathname());
            if ($imageSize) {
                [$width, $height] = $imageSize;
                
                // Minimum dimensions
                if ($width < 100 || $height < 100) {
                    $errors[] = 'Image dimensions must be at least 100x100 pixels.';
                }
                
                // Maximum dimensions
                if ($width > 4000 || $height > 4000) {
                    $errors[] = 'Image dimensions must not exceed 4000x4000 pixels.';
                }
            }
        }

        return $errors;
    }
}
