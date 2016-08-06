<?php

use Mkusher\Co;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

$loop = Factory::create();
$fs = Filesystem::create($loop);

function lsWithException($fs) {
    $list = yield $fs->dir(__DIR__)->ls();
    throw new \Exception("Test");
    return $list;
}

Co\await(function() use ($fs) {
    try {
        $list = yield lsWithException($fs);
        foreach($list as $node) {
            printf("- %s\n", $node->getPath());
        }
    } catch (\Exception $e) {
        printf("Exception: %s\n", $e->getMessage());
    }
})->then(function() {
    echo "We are done!\n";
});

Co\await(function() use ($fs) {
    $list = yield lsWithException($fs);
})->then(function() {
    echo "OK\n";
}, function() {
    echo "Not ok\n";
});

$loop->run();
