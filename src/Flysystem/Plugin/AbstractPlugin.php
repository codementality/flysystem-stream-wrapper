<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Plugin;

use League\Flysystem\Config;
//use League\Flysystem\Plugin\AbstractPlugin as FlysystemPlugin;

abstract class AbstractPlugin
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    protected function defaultConfig()
    {
        $config = new Config();
        $config->setFallback($this->filesystem->getConfig());

        return $config;
    }

    /**
     * Set the Filesystem object.
     *
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function setFilesystem(FilesystemOperator $filesystem)
    {
        $this->filesystem = $filesystem;
    }
}
