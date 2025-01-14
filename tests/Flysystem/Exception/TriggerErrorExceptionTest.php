<?php

use Lhridley\Flysystem\Exception\DirectoryExistsException;
use PHPUnit\Framework\TestCase;

class TriggerErrorExceptionTest extends TestCase {
    public function testFormat()
    {
        $e = new DirectoryExistsException();
        $this->assertSame('a(): Is a directory', $e->formatMessage('a'));
    }
}
