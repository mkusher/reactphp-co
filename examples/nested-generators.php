<?php

use Mkusher\Co;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

$loop = Factory::create();
$fs = Filesystem::create($loop);

function listFiles($fs) {
    $list = yield $fs->dir(__DIR__)->ls();
    printf("Writing in nested function\n");
    foreach($list as $node) {
        printf("- %s\n", $node->getPath());
    }
    return $list;
}

Co\await(function() use ($fs) {
    try {
        $list = yield listFiles($fs);
        printf("Writing in root function\n");
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
