<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Tests\StreamCommand;

use League\Flysystem\UnableToDeleteFile;
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\UnlinkCommand;
use Codementality\FlysystemStreamWrapper\Tests\Assert;
use PHPUnit\Framework\MockObject\MockObject;

class UnlinkTest extends AbstractStreamCommandTestCase
{
    use Assert;

    public function test(): void
    {
        $current = $this->getCurrent([
            'visibility' => 'public',
            'mimeType' => 'dontCare',
        ]);

        $this->assertTrue(UnlinkCommand::run($current, self::TEST_PATH));
    }

    public function testNotExisting(): void
    {
        $current = $this->getCurrent();

        $this->assertFalse(@UnlinkCommand::run($current, self::TEST_PATH));

        $this->expectErrorWithMessage('No such file or directory');
        UnlinkCommand::run($current, self::TEST_PATH);
    }

    public function testFailed(): void
    {
        $current = $this->getCurrent([
            'visibility' => 'public',
            'mimeType' => 'dontCare',
        ]);
        /** @var MockObject $filesystem */
        $filesystem = $current->filesystem;
        $filesystem->expects($this->exactly(2))
            ->method('delete')
            ->with('test')
            ->willThrowException(UnableToDeleteFile::atLocation(self::TEST_PATH));

        $this->assertFalse(@UnlinkCommand::run($current, self::TEST_PATH));

        $this->expectErrorWithMessage('Could not delete file');
        UnlinkCommand::run($current, self::TEST_PATH);
    }
}
