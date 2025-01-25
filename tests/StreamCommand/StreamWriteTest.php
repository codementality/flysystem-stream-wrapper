<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Tests\StreamCommand;

use Codementality\FlysystemStreamWrapper\Flysystem\FileData;
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\StreamWriteCommand;
use PHPUnit\Framework\TestCase;

class StreamWriteTest extends TestCase
{
    public function test(): void
    {
        $current = new FileData();

        $this->assertSame(0, StreamWriteCommand::run($current, 'test'));

        $current->handle = fopen('php://temp', 'wb+');
        if (!is_resource($current->handle)) {
            $this->fail();
        }

        $this->assertSame(4, StreamWriteCommand::run($current, 'test'));
        $this->assertSame(4, $current->bytesWritten);
        $this->assertSame(4, ftell($current->handle));

        $current->alwaysAppend = true;
        rewind($current->handle);
        $this->assertSame(3, StreamWriteCommand::run($current, 'xyz'));
        $this->assertSame(7, $current->bytesWritten);
        $this->assertSame(0, ftell($current->handle));
        $this->assertSame('testxyz', fread($current->handle, 1024));
    }
}
