<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Exception;

//use League\Flysystem\FilesystemException as Exception;
use RuntimeException as Exception;

class TriggerErrorException extends Exception
{
    protected $defaultMessage;

    public function formatMessage($function)
    {
        return sprintf($this->message ? $this->message : $this->defaultMessage, $function);
    }
}
