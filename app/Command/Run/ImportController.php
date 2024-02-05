<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\DbService;
use App\Services\ManagerService;
use App\Services\MollyService;

class ImportController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;
    protected DbService $db;

    final public function handle(): void
    {
        parent::handle();

        $this->manager->start(self::class);

        function ipRange($cidr) {
            $range = array();
            $cidr = explode('/', $cidr);
            $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
            $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
            return $range;
        }

        function ip_range($start, $end) {
            $start = ip2long($start);
            $end = ip2long($end);
            return array_map('long2ip', range($start, $end) );
        }

        $ips = explode("\n", file_get_contents($this->app->config->writable . "/ips.txt"));

        unset($ips[array_key_last($ips)]);

        $i = 1;
        foreach($ips as $ip){
            echo "\n\nLINE " . ++$i . "\n\n";
            $sql = [];
            $one = (ip_range(...ipRange($ip)));
            foreach($one as $o){
                $sql[] = 'INSERT INTO ips(ip) VALUES (\'' . $o . '\') ON CONFLICT DO NOTHING ;';
            }
            $sql = implode("\n", $sql);

            $this->db->executeSql($sql);
        }


        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
