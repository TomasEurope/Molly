<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class MyController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;

    final public function handle(): void
    {
        parent::handle();

        echo "\n\n";

        echo "Hi Molly :)";

        $x = geoip_database_info();

        print_r($x);


        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
