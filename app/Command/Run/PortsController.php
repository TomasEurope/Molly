<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class PortsController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;

    final public function handle(): void
    {
        parent::handle();

        $config = (object) [
            'parallel' => $this->hasParam('parallel') ? $this->getParam('parallel') : 1,
        ];

        $this->manager->start(self::class, $config, 'Parallel: ' . $config->parallel);

        $protos = [
            [
                'id' => 1,
                'proto' => 'http'
            ],
            [
                'id' => 2,
                'proto' => 'https'
            ]
        ];

        $file = [
            'id' => 1,
            'file' => '/'
        ];

        do {

            $i = 0;
            $this->molly->queueReset();
            $ip = $this->molly->getResultIpRandom()[0];
            for($port=0;$port<65536;$port++) {
                foreach ($protos as $proto){
                    $one = [
                        $proto['id'] . '-' . $ip['id'] . '-' . $port . '-' . $file['id'],
                        $proto['proto'] . '://' . $ip['ip'] . ':' . $port . $file['file']
                    ];
                    $this->molly->queueAdd($one);
                }
                $i++;
            }
            $this->molly->queueExec($config->parallel);

            sleep(1);
        } while($config = $this->manager->ping($i, 'Last: ' . $one[1] . "           "));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
