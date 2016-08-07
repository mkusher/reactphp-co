<?php

namespace Mkusher\Co;

use Generator;
use Exception;
use React\Promise;
use React\Promise\ExtendedPromiseInterface as PromiseInterface;

function await(callable $f) {
    $gen = $f();
    if ($gen instanceof Generator) {
        return waitResult($gen, $gen->current())->then(
            function ($lastPromiseResult) use ($gen) {
                return getGeneratorResult($gen, $lastPromiseResult);
            },
            function ($error) use ($gen) {
                $gen->throw($error);
            }
        );
    } else {
        return $gen;
    }
}

function waitResult(Generator $gen, $result) {
    if ($result instanceof PromiseInterface) {
        return waitPromise($gen, $result);
    } else if ($result instanceof Generator) {
        return waitPromise(
            $gen,
            waitResult($result, $result->current())->then(
                function($lastPromiseResult) use ($result) {
                    return getGeneratorResult($result, $lastPromiseResult);
                }
            )
        );
    } else {
        if($gen->valid())
            return waitResult($gen, $gen->send($result));
        return Promise\resolve($result);
    }
}

function getGeneratorResult(Generator $gen, $lastPromiseResult) {
    try {
        if (method_exists($gen, "getReturn")) {
            return $gen->getReturn();
        } else {
            return $lastPromiseResult;
        }
    } catch (Exception $e) {
        return $lastPromiseResult;
    }
}

function waitPromise(Generator $gen, PromiseInterface $promise) {
    return $promise->then(function($result) use ($gen) {
        return waitResult($gen, $gen->send($result));
    }, function($error) use ($gen) {
        $gen->throw($error);
    });
}
