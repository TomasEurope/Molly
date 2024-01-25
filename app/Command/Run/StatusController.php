<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class StatusController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;

    final public function handle(): void
    {
        parent::handle();

        $i = 0;
        $lines = 0;
        echo "\n";
        do {
            $i++;
            $lines += 2;
            echo str_repeat("\e[1A", $lines);
            $lines = 0;
            $services = $this->manager->statusAll();
            foreach($services as $service => $status){
                echo "\n\n # " . @$service . " (PID: " . @$status->pid . ") " . (file_exists("/proc/" . @$status->pid) ? "RUNNING" : "INACTIVE") . " - " . (time() - @$status->ping) . "s ago\n";
                echo @$status->status;
                $lines += 3;
            }
            echo "\n\n";
            sleep(1);
        } while($this->manager->ping($i));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
