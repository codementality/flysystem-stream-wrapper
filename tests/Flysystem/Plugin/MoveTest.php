<?php

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MoveTest extends TestCase {

    use ProphecyTrait;

    public function test()
    {
        $this->expectNotToPerformAssertions();
        $adapter = $this->prophesize(FilesystemAdapter::class);

        $filesystem = new Filesystem($adapter->reveal());
        $filesystem->move('source', 'dest');
    }
}
