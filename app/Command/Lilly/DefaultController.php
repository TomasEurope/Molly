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

    private array $results = [];

    public function handle(): void
    {
        parent::handle();

        $options = [
            'url' => $this->hasParam('url') ? (string)$this->getParam('url') : die("\n\n ### NO URL ### \n\n")
        ];

        echo "\n\n";
        echo " Hi Lilly :)\n\n";
        echo " URL: " . $options['url'] . "\n\n";

        echo " Sample:\n\n";
        $this->sample = $this->lilly->getScore($options['url'] . '?x=y', '');
        echo '* ' . $this->human($this->sample, []);

        echo "\n\n";

        $params = $this->app->config->params;

        for($i=0;$i<130; $i++){
            $this->step($options['url'], array_slice($params, 50*$i, 50));
        }

        echo "\n\n";

        print_r($this->results);

        echo "\n\n";
    }

    public function step(string $url, array $params, bool $pin = false){

        $fetch = $this->lilly->getScore($this->lilly->addPay($url, $params), $this->sample['body']);

        echo $this->human($fetch, $params);

        if(!$this->compare($fetch, $this->sample)){
            if(count($params) == 1){
                echo "\n ### RESULT: " . $params[0] . " ### \n\n";
                $this->results[] = $params[0];
                echo ": ";
                return;

            }
            echo "= ";
            $try = $this->lilly->halfParams($params);
            $this->step($url, $try[0], true);
            $this->step($url, $try[1], true);
        } else {
            echo ": ";
        }

    }

    public function compare(array $first, array $second): bool{

        foreach($first as $key => $val){
            if($key === 'body' || $key === 'hash'){
                continue;
            }
            if(in_array($key, ['html', 'text'])){
                if($val < 99){
                    //echo "\n" . $key . ' # ' . substr((string)$first[$key], 0, 10) . ' != ' . substr((string)$second[$key], 0, 10);

                    return false;
                }
            } elseif($val != $second[$key]){
                //echo "\n" . $key . ' # ' . substr((string)$first[$key], 0, 10) . ' != ' . substr((string)$second[$key], 0, 10);
                return false;
            }
        }

        return true;

    }

    public function human(array $result, array $params){
        return
            ($this->counter === 1 ? ': ' : '')
            . str_pad((string)$this->counter++, 5) . ' # '
            . str_pad((string)$result['code'], 3) . ' # '
            . str_pad((string)round($result['text'], 2), 5) . ' # '
            . str_pad((string)round($result['html'], 2), 5) . ' # '
            . str_pad((string)$result['text_size'], 5) . ' # '
            . str_pad((string)$result['html_size'], 5) . ' # '
            . substr($result['hash'], 0, 5) .

         ' ||| ' .

        str_pad((string)count($params), 5) . ' - ' . @$params[0] . ' ' . @$params[1] . ' ' . @$params[2] . "\n";

    }

}
