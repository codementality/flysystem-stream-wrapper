<?php

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Lhridley\Flysystem\Plugin\ForcedRename;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ForcedRenameTest extends TestCase {

    use ProphecyTrait;

    public function test()
    {
        $plugin = new ForcedRename();
        $adapter = $this->prophesize(AdapterInterface::class);
        $plugin->setFilesystem(new Filesystem($adapter->reveal()));

        $adapter->has('source')->willReturn(true);
        $adapter->has('dest')->willReturn(true);
        $adapter->getMetadata('source')->willReturn(['type' => 'file']);
        $adapter->getMetadata('dest')->willReturn(['type' => 'file']);
        $adapter->delete('dest')->willReturn(false);

        $this->assertFalse($plugin->handle('source', 'dest'));
    }
}
