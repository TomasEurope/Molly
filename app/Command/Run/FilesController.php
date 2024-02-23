<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class FilesController extends BaseController
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

        do {
            $i = 0;
            $this->molly->queueReset();
            $file = $this->molly->getFileRandom()[0];
            $results = $this->molly->getResultsRandom($config->count, $file->id);
            foreach($results as $result) {
                $one = [
                    $result['protos_id'] . '-' . $result['ips_id'] . '-' . $result['ports_id'] . '-' . $file['id'],
                    rtrim($result['url'], '/') . $file['file']
                ];
                $this->molly->queueAdd($one);
                $i++;
            }
            $this->molly->queueExec($config->parallel);

            sleep(1);
        } while($config = $this->manager->ping($i, 'Parallel: ' . $config->parallel . ' | Count: ' . $config->count . ' | Last: ' . substr($one[1], 60) . "           "));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
