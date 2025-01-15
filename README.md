# Flysystem stream wrapper

This package is a forked and updated version of the original package, twistor/flysystem-stream-wrapper. It
has been updated to run on PHP 8.2+, and unit tests have been uppdated for PHPUnit 11+.

![Status](https://github.com/codementality/flysystem-stream-wrapper/actions/workflows/tests.yml/badge.svg)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/codementality/flysystem-stream-wrapper.svg?style=flat-square)](https://packagist.org/packages/codementality/flysystem-stream-wrapper)

## Installation

```
composer require codementality/flysystem-stream-wrapper
```

## Usage

```php
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Codementality\FlysystemStreamWrapper;

// Get a Filesystem object.
$filesystem = new Filesystem(new Local('/some/path'));

FlysystemStreamWrapper::register('fly', $filesystem);

// Then you can use it like so.
file_put_contents('fly://filename.txt', $content);

mkdir('fly://happy_thoughts');

FlysystemStreamWrapper::unregister('fly');

```

## Notes

This project tries to emulate the behavior of the standard PHP functions,
rename(), mkdir(), unlink(), etc., as closely as possible. This includes
emitting warnings. If any differences are discovered, please file an issue.
