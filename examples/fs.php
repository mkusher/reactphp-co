<?php

use Mkusher\Co;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

$loop = Factory::create();
$fs = Filesystem::create($loop);

Co\await(function() use ($fs) {
    try {
        $list = yield $fs->dir(__DIR__)->ls();
        foreach($list as $node) {
            printf("- %s\n", $node->getPath());
        }
    } catch (\Exception $e) {
        var_dump($e);
    }
})->then(function() {
    echo "We are done!\n";
});

$loop->run();
