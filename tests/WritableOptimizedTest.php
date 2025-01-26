<?php

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use Codementality\FlysystemStreamWrapper\FlysystemStreamWrapper;

class WritableOptimizedTest extends StreamOperationTest
{
    public function setUp(): void
    {
        $this->testDir = __DIR__ . '/testdir';

        $filesystem = new Filesystem(new WritableLocal($this->testDir));
        $filesystem->deleteDirectory('testdir');
        $filesystem->createDirectory('testdir');

        $this->filesystem = $filesystem;
        FlysystemStreamWrapper::register('flysystem', $this->filesystem);
    }
}

class WritableLocal extends LocalFilesystemAdapter
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
