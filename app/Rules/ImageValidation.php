<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ImageValidation implements ValidationRule
{
    protected int $maxSize;
    protected array $allowedTypes;
    protected int $minWidth;
    protected int $minHeight;
    protected int $maxWidth;
    protected int $maxHeight;

    public function __construct(
        int $maxSize = 5242880, // 5MB
        array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        int $minWidth = 100,
        int $minHeight = 100,
        int $maxWidth = 4000,
        int $maxHeight = 4000
    ) {
        $this->maxSize = $maxSize;
        $this->allowedTypes = $allowedTypes;
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file.');
            return;
        }

        // Check if file is an image
        if (!str_starts_with($value->getMimeType(), 'image/')) {
            $fail('The :attribute must be an image.');
            return;
        }

        // Check allowed file types
        if (!in_array($value->getMimeType(), $this->allowedTypes)) {
            $fail('The :attribute must be a file of type: ' . implode(', ', $this->allowedTypes) . '.');
            return;
        }

        // Check file size
        if ($value->getSize() > $this->maxSize) {
            $maxSizeMB = round($this->maxSize / 1024 / 1024, 1);
            $fail("The :attribute must not be larger than {$maxSizeMB}MB.");
            return;
        }

        // Check image dimensions
        $imageSize = getimagesize($value->getPathname());
        if (!$imageSize) {
            $fail('The :attribute is not a valid image.');
            return;
        }

        [$width, $height] = $imageSize;

        if ($width < $this->minWidth || $height < $this->minHeight) {
            $fail("The :attribute must be at least {$this->minWidth}x{$this->minHeight} pixels.");
            return;
        }

        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $fail("The :attribute must not exceed {$this->maxWidth}x{$this->maxHeight} pixels.");
            return;
        }
    }
}
