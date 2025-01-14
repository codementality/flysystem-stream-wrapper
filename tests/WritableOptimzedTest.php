<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Lhridley\FlysystemStreamWrapper;

class WritableOptimzedTest extends StreamOperationTest
{
    public function setUp(): void
    {
        $this->testDir = __DIR__ . '/testdir';

        $filesystem = new Filesystem(new Local(__DIR__));
        $filesystem->deleteDir('testdir');
        $filesystem->createDir('testdir');

        $writable = new WritableLocal($this->testDir, \LOCK_EX, 0002, $this->perms);
        $this->filesystem = new Filesystem($writable);
        FlysystemStreamWrapper::register('flysystem', $this->filesystem);
    }
}

class WritableLocal extends Local
{
    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $location = $this->applyPathPrefix($path);
        $handle = fopen($location, 'r');

        $stream = fopen('php://temp', 'w+b');
        stream_copy_to_stream($handle, $stream);
        fclose($handle);
        fseek($stream, 0);

        return compact('stream', 'path');
    }
}
