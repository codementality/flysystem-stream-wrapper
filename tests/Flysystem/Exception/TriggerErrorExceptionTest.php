<?php

use Lhridley\Flysystem\Exception\DirectoryExistsException;

class TriggerErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $e = new DirectoryExistsException();
        $this->assertSame('a(): Is a directory', $e->formatMessage('a'));
    }
}
