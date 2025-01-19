<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Tests\FileCommand;

use Codementality\FlysystemStreamWrapper\Tests\Assert;

class IsFileTest extends AbstractFileCommandTestCase
{
    use Assert;

    public function test(): void
    {
        $file = $this->testDir->createFile();
        $this->assertFalse(is_file($file->flysystem));

        touch($file->local);
        $this->assertTrue(is_file($file->flysystem));
    }

    public function testDirectory(): void
    {
        $dir = $this->testDir->createDirectory(true);
        $this->assertFalse(is_file($dir->flysystem));
    }
}
