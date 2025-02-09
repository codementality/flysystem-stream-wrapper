<?php

use League\Flysystem\Local\LocalFilesystemAdapter as Local;
use League\Flysystem\Filesystem;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Codementality\FlysystemStreamWrapper\FlysystemStreamWrapper;

class WritableOptimizedTest extends StreamOperationTest
{
    public function setUp(): void
    {
        $this->testDir = __DIR__ . '/testdir';

        $filesystem = new Filesystem(new Local(__DIR__));
        $filesystem->deleteDirectory('testdir');
        $filesystem->createDirectory('testdir');

        $writable = new Local($this->testDir, PortableVisibilityConverter::fromArray($this->perms), \LOCK_EX, 0002);
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

    private function applyPathPrefix($path) {
        return rtrim($prefix, '\\/') . '/' . ltrim($path, '\\/');
    }
}
