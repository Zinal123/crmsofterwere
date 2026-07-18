<?php

namespace App\Repositories\Contracts;

use App\Models\JobPhoto;

interface JobPhotoRepositoryInterface
{
    public function create(array $data): JobPhoto;
}
