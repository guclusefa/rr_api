<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FileUploaderService
{
    public function uploadPhoto($photo, $photoName, $photoPath): string
    {
        // check if photo is an image
        $mimeType = $photo->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'La photo n\'est pas valide');
        }
        // check if photo is too big
        $size = $photo->getSize();
        if ($size > 1000000) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'La photo ne peut pas dépasser 1Mo');
        }
        // upload photo to path and rename photo to user id
        $photoName = $photoName . '.' . $photo->guessExtension();
        $photo->move($photoPath, $photoName);
        return $photoName;
    }

    public function deletePhoto($photoPath): void
    {
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }
}