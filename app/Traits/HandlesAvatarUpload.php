<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait HandlesAvatarUpload
{
    protected function storeAvatar(UploadedFile $file): string
    {
        $avatarName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/images/'), $avatarName);
        return $avatarName;
    }
}
