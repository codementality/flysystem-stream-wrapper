<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Tests\StreamCommand;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToDeleteDirectory;
use Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand\RmdirCommand;
use Codementality\FlysystemStreamWrapper\Tests\Assert;
use PHPUnit\Framework\MockObject\MockObject;

class RmdirTest extends AbstractStreamCommandTestCase
{
    use Assert;

    public function test(): void
    {
        $current = $this->getCurrent();
        /** @var MockObject $filesystem */
        $filesystem = $current->filesystem;
        $filesystem->expects($this->once())
            ->method('deleteDirectory')
            ->with('test');

        $this->assertTrue(RmdirCommand::run($current, $current->path, 0));
    }

    /** @return array<array<string>> */
    public static function rootDirectoryProvider(): array
    {
        return [
            [''],
            ['/'],
            ['//'],
        ];
    }

    /** @dataProvider rootDirectoryProvider */
    public function testRootDirectory(string $dir): void
    {
        $current = $this->getCurrent();

        $this->assertFalse(@RmdirCommand::run($current, self::TEST_PROTOCOL.'://'.$dir, 0));

        $this->expectErrorWithMessage('Directory is root');
        RmdirCommand::run($current, self::TEST_PROTOCOL.'://'.$dir, 0);
    }

    public function testRecursive(): void
    {
        $current = $this->getCurrent();
        /** @var MockObject $filesystem */
        $filesystem = $current->filesystem;
        $filesystem->method('listContents')
            ->willReturn(new DirectoryListing([
                new FileAttributes('one'),
            ]));

        $this->assertTrue(RmdirCommand::run($current, $current->path, STREAM_MKDIR_RECURSIVE));

        $this->assertFalse(@RmdirCommand::run($current, $current->path, 0));

        $this->expectErrorWithMessage('Directory not empty');
        RmdirCommand::run($current, $current->path, 0);
    }

    public function testRemoteFail(): void
    {
        $current = $this->getCurrent();
        /** @var MockObject $filesystem */
        $filesystem = $current->filesystem;
        $filesystem->method('deleteDirectory')
            ->willThrowException(
                UnableToDeleteDirectory::atLocation(self::TEST_PATH)
            );

        $this->assertFalse(@RmdirCommand::run($current, $current->path, 0));

        $this->expectErrorWithMessage('/(Could not remove directory|Directory not empty)/');
        RmdirCommand::run($current, $current->path, 0);
    }
}
