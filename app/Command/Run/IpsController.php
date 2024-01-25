<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class IpsController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;

    final public function handle(): void
    {
        parent::handle();

        $config = (object) [
            'parallel' => $this->hasParam('parallel') ? $this->getParam('parallel') : 1,
            'count' => $this->hasParam('count') ? $this->getParam('count') : 100
        ];

        $this->manager->start(self::class, $config, 'Parallel: ' . $config->parallel);

        $file = ['id' => 0, 'file' => '/'];
        $proto = ['id' => 0, 'proto' => 'http'];

        do {

            $i = 0;
            $this->molly->queueReset();
            //$proto = $this->molly->getProtoRandom()[0];
            $port = $this->molly->getPortRandom()[0];
            $ips = $this->molly->getIpsRandom($config->count);
            $this->molly->markUpdated('ips', array_column($ips, 'id'));

            // Nice :)
            if(@$_GET['MollyLovesYou']){
                echo json_encode($ips);
                exit;
            }
            foreach($ips as $ip) {
                $one = [
                    $proto['id'] . '-' . $ip['id'] . '-' . $port['id'] . '-' . $file['id'],
                    //$proto['proto'] . '://' . $ip['ip'] . ':' . $port['port'] . $file['file']
                    $proto['proto'] . '://' . $ip['ip'] . ':' . $port['port'] . $file['file']
                ];
                $this->molly->queueAdd($one);
                $i++;
            }
            $this->molly->queueExec($config->parallel);

            sleep(1);
        } while($config = $this->manager->ping($i, 'Parallel: ' . $config->parallel . ' | Count: ' . $config->count . ' | Last: ' . $one[1] . "           "));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
