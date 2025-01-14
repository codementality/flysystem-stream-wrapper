<?php

namespace Lhridley\Flysystem\Exception;

class DirectoryExistsException extends TriggerErrorException
{
    protected $defaultMessage = '%s(): Is a directory';
}
