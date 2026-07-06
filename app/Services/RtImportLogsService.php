<?php

namespace App\Services;

use App\Repositories\RtImportLogsRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class RtImportLogsService extends BaseService
{
    public function __construct(RtImportLogsRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getPaginatedWithUser(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->allWithUser($filters);
    }
}
