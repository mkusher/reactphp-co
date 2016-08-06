Reactphp Co
===========

Simple coroutines for your reactphp applications.

Installation
============

Use composer to install this package

```
composer require mkusher/react-co
```

Coroutines
==========

Don't know what coroutines are? Read this [awesome article by Nikita Popov](http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html)

Examples
========

Basic example where `asyncOp1` and `asyncOp2` your asynchronous functions that
return instance of PromiseInterface.

```php

use Mkusher\Co;

Co\await(function() {
    $a = yield asyncOp1();
    $b = yield asyncOp2($a);
});
```

`await` returns Promise, so that you can wait for all your operatoins to complete like:

```php

use Mkusher\Co;

Co\await(function() {
    $a = yield asyncOp1();
    return "10";
})->then(function($result) {
    echo $result;
});
```

This example will write "10" after executing `asyncOp1`.

You can find more examples in [examples dir](https://github.com/mkusher/reactphp-co/blob/master/examples/)
