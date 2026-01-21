<?php

namespace App\Services;

class ImageUploadService
{
    public function uploadImage($file, $path = 'uploads')
    {
        if (!$file->isValid() || $file->hasMoved()) {
            throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . $path, $newName);

        return $path . '/' . $newName;
    }
}
