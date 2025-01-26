<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Exception;

class DirectoryExistsException extends TriggerErrorException
{
    protected $defaultMessage = '%s(): Is a directory';
}
