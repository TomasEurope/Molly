<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;
use stdClass;

class ManagerService implements ServiceInterface
{
    private App $app;
    private int $id;
    private string $name;

    final public function load(App $app): void
    {
        $this->app = $app;
        $this->id = mt_rand();
    }

    final public function start(string $name, object $config = new stdClass(), string $options = ''): bool
    {
        $this->setName($name);
        $file = $this->getFilename() . '.json';

        if(@!$_GET['MollyLovesYou']) {
            echo "\nStarting " . $this->name . " ID: " . $this->id . " | " . $options . "\n\n";
        }

        @file_put_contents($file, json_encode([
            'id' => $this->id,
            'start' => time(),
            'ping' => time(),
            'config' => $config,
            'pid' => getmypid()
        ], JSON_THROW_ON_ERROR));

        return true;
    }

    final public function ping(int $count, string $options = ''): bool|object
    {
        if(@$_GET['MollyLovesYou']){
            echo "\n\n ########## PING END #######\n\n";
            exit;
        }
        $file = $this->app->config->writable . '/' . $this->name . '.json';
        $json = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);

        if(empty($json->total)) {
            $json->total = 0;
        }
        $json->ping = time();
        $json->total += $count;

        $json->status = "\rSpeed: " . round($json->total / (time() - $json->start)) . "/s | Load: " . round(sys_getloadavg()[0] / $this->countCpus(), 2) . " | Total: " . round($json->total / 1000) . "k | Time: " . round((time() - $json->start) / 3600, 1) . "h"  . " | " . $options;

        echo $json->status;

        file_put_contents($file, json_encode($json, JSON_THROW_ON_ERROR));
        return $json->config;
    }

    private function setName(string $name): void
    {
        $this->name = substr(strrchr($name, '\\'), 1);
    }

    private function countCpus(): int
    {
        $ncpu = 1;
        if(is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $ncpu = count($matches[0]);
        }

        return $ncpu;
    }

    public function configSet(array $config)
    {
        $json = json_decode(file_get_contents($this->getFilename() . '.json'), false, 512, JSON_THROW_ON_ERROR);
        $json->config = array_merge($json->config, $config);
        file_put_contents($this->getFilename() . '.json', json_encode($json, JSON_THROW_ON_ERROR));
    }

    public function configGet()
    {
        $json = json_decode(file_get_contents($this->getFilename() . '.json'), false, 512, JSON_THROW_ON_ERROR);
        return $json->config;
    }

    private function getFilename(): string
    {
        return $this->app->config->writable . '/' . $this->name;
    }

    public function statusAll(): array
    {
        $services = [];
        $files = scandir($this->app->config->writable);
        foreach($files as $file){
            if(!str_ends_with($file, 'json')){
                continue;
            }
            $services[str_replace('.json', '', $file)] = json_decode(file_get_contents($this->app->config->writable . '/' . $file));
        }

        return $services;
    }
}
