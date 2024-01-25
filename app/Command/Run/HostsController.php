<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\DbService;
use App\Services\ManagerService;
use App\Services\MollyService;

class HostsController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;
    protected DbService $db;

    final public function handle(): void
    {
        parent::handle();

        $config = (object) [
            'count' => $this->hasParam('count') ? $this->getParam('count') : 10000
        ];

        $this->manager->start(self::class, $config, );

        $i = 0;

        do {

            echo "\nSTEP count $i \n";
            $ips = $this->molly->getIpsRandom($config->count, 'host IS NULL');
            $sql = [];
            foreach($ips as $ip) {
                $host = gethostbyaddr($ip['ip']);
                $new = gethostbynamel($host);
                if($new) {
                    foreach ($new as $n) {
                        if ($n == $ip['ip']) {
                            continue;
                        }
                        echo '.';
                        $sql[] = 'INSERT INTO ips(ip, level, parent) VALUES (\'' . $n . '\', 2, ' . $ip['id'] . ') ON CONFLICT DO NOTHING ;';
                    }
                }
                if($ip['ip'] == $host){
                    $host = 0;
                }
                echo "#";
                $sql[] = 'UPDATE ips SET host=\'' . $host . '\' WHERE id=' . $ip['id'] . ';';
                $i++;
            }

            echo "\nSQL " . count($sql) . "\n";

            $sql = implode("\n", $sql);

            $this->db->executeSql($sql);

            sleep(1);
        } while($config = $this->manager->ping($i, 'Last: ' . $ip['ip'] . "           "));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
