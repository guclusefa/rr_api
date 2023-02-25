<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileUploaderService
{
    public function __construct
    (
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function uploadPhoto($photo, $photoName, $photoPath): string
    {
        // check if photo is an image
        $mimeType = $photo->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.file_uploader.unvalid_photo')
            );
        }
        // check if photo is too big
        $size = $photo->getSize();
        $maxSize = 5000000;
        if ($size > $maxSize) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.file_uploader.unvalid_photo_size', ['%max_size%' => $maxSize / 1000000])
            );
        }
        // upload photo to path and rename photo to user id
        $photoName = $photoName . '.' . $photo->guessExtension();
        $photo->move($photoPath, $photoName);
        return $photoName;
    }

    public function uploadMedia($media, $mediaName, $mediaPath): string
    {
        // check if media is a video
        $mimeType = $media->getMimeType();
        if (!in_array($mimeType, ['video/mp4', 'video/mp3', 'image/jpeg', 'image/png', 'image/gif'])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.file_uploader.unvalid_media')
            );
        }
        // check if media is too big
        $size = $media->getSize();
        // 10MB
        $maxSize = 10000000;
        if ($size > $maxSize) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.file_uploader.unvalid_media_size', ['%max_size%' => $maxSize / 1000000])
            );
        }
        // upload media to path and rename media to user id
        $mediaName = $mediaName . '.' . $media->guessExtension();
        $media->move($mediaPath, $mediaName);
        return $mediaName;
    }

    public function deleteFile($filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}