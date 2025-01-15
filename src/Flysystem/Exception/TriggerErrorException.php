<?php

namespace Codementality\Flysystem\Exception;

use League\Flysystem\Exception;

class TriggerErrorException extends Exception
{
    protected $defaultMessage;

    public function formatMessage($function)
    {
        return sprintf($this->message ? $this->message : $this->defaultMessage, $function);
    }
}
