<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '.' . $file->guessExtension();
        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            return $e->getMessage();
        }
        return $targetDirectory . $fileName;
    }
}
