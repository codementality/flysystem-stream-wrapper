<?php

namespace Lhridley\Flysystem\Exception;

class NotADirectoryException extends TriggerErrorException
{
    protected $defaultMessage = '%s(): Not a directory';
}
