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
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\StreamTellCommand;
use PHPUnit\Framework\TestCase;

class StreamTellTest extends TestCase
{
    public function test(): void
    {
        $current = new FileData();

        $this->assertSame(0, StreamTellCommand::run($current));

        $current->handle = fopen(__FILE__, 'rb');
        if (!is_resource($current->handle)) {
            $this->fail();
        }

        fread($current->handle, 42);
        $this->assertSame(42, StreamTellCommand::run($current));

        $current->alwaysAppend = true;
        $current->writeOnly = true;
        $this->assertSame(0, StreamTellCommand::run($current));
    }
}
