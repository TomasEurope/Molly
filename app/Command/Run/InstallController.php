<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\DbService;
use App\Services\ManagerService;
use App\Services\MollyService;

class InstallController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;
    protected DbService $db;

    final public function handle(): void
    {
        parent::handle();

        $this->addPorts();

        $this->addFiles();

        $this->db->executeSql('INSERT INTO "protos" ("id", "proto", "options", "enabled") VALUES
(1,	\'http\',	NULL,	1),
(2,	\'https\',	NULL,	1);');

        echo "\n\n ### END ### \n\n";
        die(0);
    }

    private function addPorts(){

        for($i=1;$i<65536;$i++) {
            $sql[] = 'INSERT INTO ports(id) VALUES (\'' . $i . '\') ON CONFLICT DO NOTHING ;';
        }

        $sql[] = 'UPDATE ports SET enabled=1 WHERE id=80 OR id=443 OR id=8080 OR id=8443;';

        $this->db->executeSql(implode("\n", $sql));

        echo "\nPorts done\n";
    }

    private function addFiles(){

        $files = explode("\n", file_get_contents($this->app->config->base_path . '/files.txt'));

        foreach($files as $file){
            $sql[] = 'INSERT INTO files(file) VALUES (\'' . $file . '\') ON CONFLICT DO NOTHING ;';
        }

        $this->db->executeSql(implode("\n", $sql));

        echo "\nFiles done\n";
    }

}
