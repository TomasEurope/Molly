<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require __DIR__ . '/vendor/autoload.php';

use App\Config\TermwindOutputHandler;
use Minicli\App;
use Minicli\Exception\CommandNotFoundException;

$app = new App();

// Nice :)
if(@$_GET['MollyLovesYou']){
    $argv = explode(' ', $_GET['MollyLovesYou']);
    $argv = array_merge([0=>' '], $argv);
}

try {
    $app->runCommand($argv);
} catch (CommandNotFoundException $notFoundException) {
    $app->error("Command Not Found.");
    return 1;
} catch (Exception $exception) {
    if ($app->config->debug) {
        $app->error("An error occurred:");
        $app->error($exception->getMessage());
    }
    return 1;
}

return 0;
