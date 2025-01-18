<?php

namespace Codementality\Flysystem\Plugin;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Util;

class Mkdir extends AbstractPlugin
{
    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return 'mkdir';
    }

    /**
     * Creates a directory.
     *
     * @param string $dirname
     * @param int $mode
     * @param int $options
     *
     * @return bool True on success, false on failure.
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function handle($dirname, $mode, $options)
    {
        $dirname = Util::normalizePath($dirname);

        $adapter = $this->filesystem->getAdapter();

        // If recursive, or a single level directory, just create it.
        if (($options & STREAM_MKDIR_RECURSIVE) || strpos($dirname, '/') === false) {
            // @deprecated replace createDir with createDirectory.
            return (bool) $adapter->createDir($dirname, $this->defaultConfig());
        }
        // @deprecated replace has with directoryExists.
        if ( ! $adapter->has(dirname($dirname))) {
            throw new FileNotFoundException($dirname);
        }
        // @deprecated replace createDir with createDirectory.
        return (bool) $adapter->createDir($dirname, $this->defaultConfig());
    }
}
