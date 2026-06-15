<?php

namespace App\Services;

use App\Repositories\RtDimSubGolonganRepository;

class RtDimSubGolonganService extends BaseService
{
    public function __construct(RtDimSubGolonganRepository $repository)
    {
        parent::__construct($repository);
    }
}
