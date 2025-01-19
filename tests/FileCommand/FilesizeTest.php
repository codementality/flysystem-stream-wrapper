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

class FilesizeTest extends AbstractFileCommandTestCase
{
    use Assert;

    public function test(): void
    {
        $file = $this->testDir->createFile(true);
        $this->assertSame(filesize($file->local), filesize($file->flysystem));
    }

    public function testFailed(): void
    {
        $file = $this->testDir->createFile();
        $this->assertFalse(@filesize($file->flysystem));

        $this->expectErrorWithMessage('Stat failed');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $size = filesize($file->flysystem);
    }
}
