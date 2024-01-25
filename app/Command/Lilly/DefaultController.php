<?php

declare(strict_types=1);

namespace App\Command\Lilly;

use App\Command\BaseController;
use App\Services\LillyService;

class DefaultController extends BaseController
{

    protected LillyService $lilly;
    private array $sample;

    private int $counter = 0;

    public function handle(): void
    {
        parent::handle();

        $options = [
            'url' => $this->hasParam('url') ? (string)$this->getParam('url') : die("\n\n ### NO URL ### \n\n")
        ];

        echo "\n\n";
        echo "Hi Lilly :)\n\n";
        echo " URL: " . $options['url'] . "\n\n";

        echo "Sample:\n";
        $this->sample = $this->lilly->getScore($options['url'] . '?x=y', '');
        echo $this->human($this->sample);

        echo "\n\n";

        $params = $this->app->config->params;

        for($i=0;$i<130; $i++){
            $this->step($options['url'], array_slice($params, 50*$i, 50));
        }

        echo "\n\n";
    }

    public function step(string $url, array $params, bool $pin = false){

        $fetch = $this->lilly->getScore($this->lilly->addPay($url, $params), $this->sample['body']);

        echo $this->human($fetch);

        if(!$this->compare($fetch, $this->sample)){
            if(count($params) == 1){
                echo "\n\n\n ### RESULT: " . $params[0] . " ### \n\n\n";
                return;

            }
            if(1) {
                echo "\n\nLilly Found :) \n\n\n";
            }
            $try = $this->lilly->halfParams($params);
            $this->step($url, $try[0], true);
            $this->step($url, $try[1], true);
        }

    }

    public function compare(array $first, array $second): bool{


        return json_encode($first) === json_encode($second);

    }

    public function human(array $result){
        return
            $this->counter++ . ' # '
            . $result['code'] . ' # '
            . round($result['text'], 2) . ' # '
            . round($result['html'], 2) . ' # '
            . $result['text_size'] . ' # '
            . $result['html_size'] . ' # '
            . $result['hash'] . "\n";

    }

}
