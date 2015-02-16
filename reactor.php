#!/usr/bin/env php
<?php
// print_r(scandir(__DIR__.'/../vendor/'));

include_once __DIR__.'/vendor/autoload.php';

$reactor = new D2G\Reactor\Reactor($_SERVER['argv']);

$reactor->ignite();