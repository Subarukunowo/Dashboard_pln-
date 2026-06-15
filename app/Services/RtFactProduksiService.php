<?php

namespace App\Services;

use App\Repositories\RtFactProduksiRepository;

class RtFactProduksiService extends BaseService
{
    public function __construct(RtFactProduksiRepository $repository)
    {
        parent::__construct($repository);
    }
}