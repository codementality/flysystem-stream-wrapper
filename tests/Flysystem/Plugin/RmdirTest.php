<?php

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class RmdirTest extends TestCase {

    use ProphecyTrait;

    public function test()
    {
        $adapter = $this->prophesize(FilesystemAdapter::class);
        $filesystem = new Filesystem($adapter->reveal());
        $adapter->directoryExists('path')->willReturn(false);
        $adapter->deleteDirectory('path')->shouldBeCalled();
        $filesystem->deleteDirectory('path');
        $this->assertFalse($filesystem->directoryExists('path'));
    }
}
