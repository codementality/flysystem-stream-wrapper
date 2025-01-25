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
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\StreamCastCommand;
use PHPUnit\Framework\TestCase;

class StreamCastTest extends TestCase
{
    public function test(): void
    {
        $current = new FileData();

        $this->assertFalse(StreamCastCommand::run($current, STREAM_CAST_FOR_SELECT));

        $current->handle = fopen('php://temp', 'wb');

        $this->assertIsResource(StreamCastCommand::run($current, STREAM_CAST_FOR_SELECT));
    }
}
