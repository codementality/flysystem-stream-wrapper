<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codementality\FlysystemStreamWrapper\Flysystem\StreamCommand;

use League\Flysystem\FilesystemException;
use Codementality\FlysystemStreamWrapper\Flysystem\Exception\CouldNotDeleteFileException;
use Codementality\FlysystemStreamWrapper\Flysystem\Exception\FileNotFoundException;
use Codementality\FlysystemStreamWrapper\Flysystem\FileData;

final class UnlinkCommand
{
    use ExceptionHandler;

    public const UNLINK_COMMAND = 'unlink';

    public static function run(FileData $current, string $path): bool
    {
        $current->setPath($path);

        if (!file_exists($current->path)) {
            return self::triggerError(FileNotFoundException::atLocation(self::UNLINK_COMMAND, $current->path));
        }

        try {
            $current->filesystem->delete($current->file);

            return true;
        } catch (FilesystemException $e) {
            return self::triggerError(
                CouldNotDeleteFileException::atLocation(self::UNLINK_COMMAND, $current->path, $e)
            );
        }
    }
}
