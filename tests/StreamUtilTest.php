<?php

namespace Codementality\StreamUtil\Tests;

use PHPUnit\Framework\TestCase;
use Codementality\FlysystemStreamWrapper\FlysystemStreamWrapper;

class StreamUtilTest extends \PHPUnit\Framework\TestCase
{
    protected $stream;

    protected $streamWrapper;

    public function setUp(): void {
        $this->stream = fopen('data://text/plain,aaaaaaaaaa', 'r+');
        $this->streamWrapper = new FlysystemStreamWrapper();
    }

    public function tearDown(): void {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function testClone()
    {
        fseek($this->stream, 2);

        //$cloned = StreamUtil::copy($this->stream, false);
        $cloned = $this->streamWrapper->copy($this->stream, false);

        // Test seeking, and not closing.
        $this->assertSame(2, ftell($cloned));
        $this->assertSame(ftell($this->stream), ftell($cloned));

        // Test auto-closing.
        $cloned = $this->streamWrapper->copy($this->stream);

        $this->assertSame(2, ftell($cloned));
        $this->assertFalse(is_resource($this->stream));
    }

    public function testGetSize()
    {
        // fstat() doesn't work for data streams in HHVM.
        $stream = fopen('php://temp', 'w+b');
        $this->assertSame(0, $this->streamWrapper->getSize($stream));

        fwrite($stream, 'aaaaaaaaaa');
        $this->assertSame(10, $this->streamWrapper->getSize($stream));
        fclose($stream);
    }

    public function testIsWritable()
    {
        $this->assertTrue($this->streamWrapper->isWritable($this->stream));

        $appendable = fopen('data://text/plain,aaaaaaaaaa', 'a');
        $this->assertTrue($this->streamWrapper->isWritable($appendable));
        fclose($appendable);

        $not_writable = fopen('data://text/plain,aaaaaaaaaa', 'r');
        $this->assertFalse($this->streamWrapper->isWritable($not_writable));
        fclose($not_writable);
    }

    public function testTryRewind()
    {
        $this->assertTrue($this->streamWrapper->tryRewind($this->stream));
        $this->assertSame(0, ftell($this->stream));

        fseek($this->stream, 1);
        $this->assertTrue($this->streamWrapper->tryRewind($this->stream));
        $this->assertSame(0, ftell($this->stream));
    }

    public function testTrySeek()
    {
        $this->assertTrue($this->streamWrapper->trySeek($this->stream, 0));
        $this->assertSame(0, ftell($this->stream));

        $this->assertTrue($this->streamWrapper->trySeek($this->stream, 10));
        $this->assertSame(10, ftell($this->stream));

        // Rewind.
        $this->assertTrue($this->streamWrapper->trySeek($this->stream, 0));

        $this->assertTrue($this->streamWrapper->trySeek($this->stream, 0, SEEK_END));
        $this->assertSame(10, ftell($this->stream));
    }

    public function testModeIsAppendOnly()
    {
        $this->assertTrue($this->streamWrapper->modeIsAppendOnly('a'));
        $this->assertTrue($this->streamWrapper->modeIsAppendOnly('ab'));
        $this->assertFalse($this->streamWrapper->modeIsAppendOnly('a+'));
        $this->assertFalse($this->streamWrapper->modeIsAppendOnly('w'));
        $this->assertFalse($this->streamWrapper->modeIsAppendOnly('rb'));
    }

    public function testModeIsReadOnly()
    {
        $this->assertTrue($this->streamWrapper->modeIsReadOnly('r'));
        $this->assertFalse($this->streamWrapper->modeIsReadOnly('r+'));
        $this->assertFalse($this->streamWrapper->modeIsReadOnly('w'));
        $this->assertFalse($this->streamWrapper->modeIsReadOnly('w+b'));
    }

    public function testModeIsWriteOnly()
    {
        $this->assertTrue($this->streamWrapper->modeIsWriteOnly('wb'));
        $this->assertTrue($this->streamWrapper->modeIsWriteOnly('c'));
        $this->assertTrue($this->streamWrapper->modeIsWriteOnly('xt'));
        $this->assertTrue($this->streamWrapper->modeIsWriteOnly('a'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('w+'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('r'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('r+b'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('w+'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('c+'));
        $this->assertFalse($this->streamWrapper->modeIsWriteOnly('a+t'));
    }
}