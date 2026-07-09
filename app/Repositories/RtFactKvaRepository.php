<?php

namespace App\Repositories;

use App\Models\RtFactKva;

class RtFactKvaRepository extends BaseRepository
{
    public function __construct(RtFactKva $model)
    {
        parent::__construct($model);
    }
}