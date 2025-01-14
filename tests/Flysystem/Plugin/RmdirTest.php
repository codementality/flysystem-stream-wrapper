<?php

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Lhridley\Flysystem\Plugin\Rmdir;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class RmdirTest extends TestCase {

    use ProphecyTrait;

    public function test()
    {
        $plugin = new Rmdir();
        $adapter = $this->prophesize(AdapterInterface::class);
        $plugin->setFilesystem(new Filesystem($adapter->reveal()));

        $adapter->deleteDir('path')->willReturn(true);

        $this->assertTrue($plugin->handle('path', STREAM_MKDIR_RECURSIVE));
    }
}
