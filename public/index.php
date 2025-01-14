<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Twistor\FlysystemStreamWrapper;

require __DIR__ . '/../vendor/autoload.php';

// Get a Filesystem object.
$filesystem = new Filesystem(new Local(__DIR__ . '/some/path'));

FlysystemStreamWrapper::register('fly', $filesystem);
$content = "This is simple text content.";
// Then you can use it like so.
// fopen() => stream_open, fwrite() => stream_write, fclose() => stream_close
file_put_contents('fly://filename.txt', $content);

mkdir('fly://happy_thoughts');

FlysystemStreamWrapper::unregister('fly');
