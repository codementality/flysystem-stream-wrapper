<?php

namespace Codementality\Flysystem\Plugin;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Util;
use League\Flysystem\AdapterInterface;

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
     * @param \League\Flysystem\AdapterInterface $adapter
     *
     * @return bool True on success, false on failure.
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function handle($dirname, $mode, $options, $adapter)
    {

        // If recursive, or a single level directory, just create it.
        if (($options & STREAM_MKDIR_RECURSIVE) || strpos($dirname, '/') === false) {
            return (bool) $adapter->createDir($dirname, $this->defaultConfig());
        }

        if ( ! $adapter->has(dirname($dirname))) {
            throw new FileNotFoundException($dirname);
        }

        return (bool) $adapter->createDir($dirname, $this->defaultConfig());
    }
}
