<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Exception;

class NotADirectoryException extends TriggerErrorException
{
    protected $defaultMessage = '%s(): Not a directory';
}
