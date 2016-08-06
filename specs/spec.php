<?php

use Mkusher\Co;
use React\EventLoop\Factory;
use React\Promise;
use Webmozart\Assert\Assert;

require dirname(__DIR__) . '/vendor/autoload.php';

$loop = Factory::create();

it("waits for all promises and return result", Co\await(function() {
    $wait1 = yield Promise\resolve("123");
    $wait2 = yield Promise\resolve("456");
    return $wait1 . $wait2;
})->then(function($result) {
    Assert::same($result, "123456");
}));

it("catches rejected promises", Co\await(function() {
    try {
        $wait1 = yield Promise\reject(new \Exception("Some error"));
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return "Not caught";
})->then(function($result) {
    Assert::same($result, "Some error");
}));

it("returns non-promises as is", Co\await(function() {
    $wait1 = yield "123";
    $wait2 = yield Promise\resolve("456");
    return $wait1 . $wait2;
})->then(function($result) {
    Assert::same($result, "123456");
}));

it("returns null if no return", Co\await(function() {
    $wait1 = yield Promise\resolve("123");
    yield $wait1 . "456";
})->then(function($result) {
    Assert::same($result, null);
}));

it("waits for nested generators", Co\await(function() {
    $nestedCoroutine = function() {
        $wait1 = yield Promise\resolve("123");
        $wait2 = yield Promise\resolve("456");
        return $wait1 . $wait2;
    };
    $message1 = yield $nestedCoroutine();
    $message2 = yield $nestedCoroutine();
    return $message1 . $message2;
})->then(function($result) {
    Assert::same($result, "123456123456");
}));

it("passes exception to nested coroutine", Co\await(function() {
    $nestedCoroutine = function() {
        try {
            yield Promise\reject(new \Exception("Some error"));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return "Uncaught";
    };
    $message = yield $nestedCoroutine();
    return $message;
})->then(function($result) {
    Assert::same($result, "Some error");
}));

it("passes exception to parent coroutine if nested haven't caught", Co\await(function() {
    $nestedCoroutine = function() {
        yield Promise\reject(new \Exception("Some error"));
        return "Uncaught in nested coroutine";
    };
    try {
        $message = yield $nestedCoroutine();
        return "Uncaught in parent coroutine";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
})->then(function($result) {
    Assert::same($result, "Some error");
}));

$loop->run();

printf("%s %s successful\n", $okSpecs, $okSpecs === 1 ? "is": "are");

$okSpecs = 0;
function ok() {
    global $okSpecs;
    ++$okSpecs;
}

function it($name, $specPromise) {
    $specPromise->done(function() {
        ok();
    }, function($error) use ($name) {
        printf("[x] Spec \"it %s\" errored with:\n", $name);
        throw $error;
    });
}
