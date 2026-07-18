<?php

namespace App\Repositories\Eloquent;

use App\Models\JobPhoto;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;

class EloquentJobPhotoRepository implements JobPhotoRepositoryInterface
{
    public function create(array $data): JobPhoto
    {
        return JobPhoto::create($data);
    }
}
