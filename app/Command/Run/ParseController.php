<?php

namespace App\Command\Run;

use App\Command\BaseController;
use App\Services\ManagerService;
use App\Services\MollyService;

class ParseController extends BaseController
{
    protected ManagerService $manager;
    protected MollyService $molly;

    final public function handle(): void
    {
        parent::handle();

        $this->manager->start(self::class);

        do {

            $last = '';
            $i = 0;
            $dirs = scandir($this->app->config->output);
            echo " - DIRS: " . count($dirs) . " - ";
            shuffle($dirs);
            foreach($dirs as $dir){
                if($dir === '.' || $dir === '..'){
                    continue;
                }

                echo " DIR $dir ";
                $files = scandir($this->app->config->output . '/' . $dir);
                if(!$files){
                    sleep(1);
                    continue;
                }
                shuffle($files);
                foreach($files as $file){
                    if($file === '.' || $file === '..' || $file === 'options.txt'){
                        continue;
                    }
                    echo " $file ";
                    $name = explode('-', str_replace('-.txt', '', $file));
                    if(is_dir($this->app->config->output . '/' . $dir . '/' . $file)){
                            $this->molly->recurseRmdir($this->app->config->output . '/' . $dir . '/' . $file);
                    }
                    $result = file_get_contents($this->app->config->output . '/' . $dir . '/' . $file);
                    $this->molly->parse($result, ...$name);
                    @unlink($this->app->config->output . '/' . $dir . '/' . $file);
                    $i++;
                    $last = $file ?: $last;
                }

                @unlink($this->app->config->output . '/' . $dir . '/options.txt');
                @rmdir($this->app->config->output . '/' . $dir);
                break;
            }

            sleep(1);
        } while($this->manager->ping($i, 'Last: ' . $last . "           "));

        echo "\n\n ### END ### \n\n";
        die(0);
    }
}
