<?php

namespace App\Providers;

use App\Repositories\RtDimSubGolonganRepository;
use App\Repositories\RtFactBabinRepository;
use App\Repositories\RtFactBppRepository;
use App\Repositories\RtFactHjrRepository;
use App\Repositories\RtFactJamNyalaRepository;
use App\Repositories\RtFactLossesRepository;
use App\Repositories\RtFactP2tlRepository;
use App\Repositories\RtFactPelangganRepository;
use App\Repositories\RtFactPendapatanRepository;
use App\Repositories\RtFactPenjualanRepository;
use App\Repositories\RtFactRpkwhRepository;
use App\Repositories\RtImportLogsRepository;
use App\Services\RtDimSubGolonganService;
use App\Services\RtFactBabinService;
use App\Services\RtFactBppService;
use App\Services\RtFactHjrService;
use App\Services\RtFactJamNyalaService;
use App\Services\RtFactLossesService;
use App\Services\RtFactP2tlService;
use App\Services\RtFactPelangganService;
use App\Services\RtFactPendapatanService;
use App\Services\RtFactPenjualanService;
use App\Services\RtFactRpkwhService;
use App\Services\RtImportLogsService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Dim
        $this->app->bind(RtDimSubGolonganRepository::class);
        $this->app->bind(RtDimSubGolonganService::class);

        // Fact tables
        foreach ([
            [RtFactBabinRepository::class,     RtFactBabinService::class],
            [RtFactBppRepository::class,        RtFactBppService::class],
            [RtFactHjrRepository::class,        RtFactHjrService::class],
            [RtFactJamNyalaRepository::class,   RtFactJamNyalaService::class],
            [RtFactLossesRepository::class,     RtFactLossesService::class],
            [RtFactP2tlRepository::class,       RtFactP2tlService::class],
            [RtFactPelangganRepository::class,  RtFactPelangganService::class],
            [RtFactPendapatanRepository::class, RtFactPendapatanService::class],
            [RtFactPenjualanRepository::class,  RtFactPenjualanService::class],
            [RtFactRpkwhRepository::class,      RtFactRpkwhService::class],
            [RtImportLogsRepository::class,     RtImportLogsService::class],
        ] as [$repo, $service]) {
            $this->app->bind($repo);
            $this->app->bind($service);
        }
    }
}
