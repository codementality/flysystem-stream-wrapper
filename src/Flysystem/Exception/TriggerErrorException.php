<?php

namespace Codementality\FlysystemStreamWrapper\Flysystem\Exception;

class TriggerErrorException extends \RuntimeException
{
    protected $defaultMessage;

    public function formatMessage($function)
    {
        return sprintf($this->message ? $this->message : $this->defaultMessage, $function);
    }
}
