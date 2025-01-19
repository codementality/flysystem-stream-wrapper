<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Tests\FileCommand;

class ReadFileTest extends AbstractFileCommandTestCase
{
    public function test(): void
    {
        $file = $this->testDir->createFile(true);
        $content = (string) file_get_contents($file->local);
        $size = readfile($file->flysystem);
        $this->expectOutputString($content);
        $this->assertSame(strlen($content), $size);
    }
}
