<?php

declare(strict_types=1);

use App\Services\DbService;
use App\Services\ManagerService;
use App\Services\MollyService;
use App\Services\LillyService;

return [
    /****************************************************************************
     * Application Services
     * --------------------------------------------------------------------------
     *
     * The services to be loaded for your application.
     *****************************************************************************/

    'services' => [
        'db' => DbService::class,
        'molly' => MollyService::class,
        'manager' => ManagerService::class,

        'lilly' => LillyService::class
    ],
];
