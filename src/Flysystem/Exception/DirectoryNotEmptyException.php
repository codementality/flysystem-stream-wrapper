<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Exception;

class DirectoryNotEmptyException extends TriggerErrorException
{
    protected $defaultMessage = '%s(): Directory not empty';
}
