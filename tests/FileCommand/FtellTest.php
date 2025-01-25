<?php
/*
 * This file is part of the flysystem-stream-wrapper package.
 *
 * (c) 2021-2023 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\FlysystemStreamWrapper\Tests\FileCommand;

class FtellTest extends AbstractFileCommandTestCase
{
    use DataProvider;

    /**
     * @dataProvider readOnlyModeProvider
     * @dataProvider readWriteModeProvider
     * @dataProvider writeOnlyModeProvider
     */
    public function test(string $mode = 'a'): void
    {
        $file = $this->testDir->createFile(true);
        $handle = fopen($file->flysystem, $mode);
        if (!is_resource($handle)) {
            $this->fail();
        }

        fseek($handle, 42);
        if ('w' === $mode[0]) {
            if (version_compare(PHP_VERSION, '8.3.0') >= 0) {
                $this->assertSame(42, ftell($handle));

                fwrite($handle, 'test');
                $this->assertSame(46, ftell($handle));
            } else {
                $this->assertSame(0, ftell($handle));

                fwrite($handle, 'test');
                $this->assertSame(4, ftell($handle));
            }
        } elseif ('a' === $mode) {
            $this->assertSame(0, ftell($handle));

            fwrite($handle, 'test');
            $this->assertSame(4, ftell($handle));
        } elseif ('c' === $mode) {
            $this->assertSame(42, ftell($handle));

            fwrite($handle, 'test');
            $this->assertSame(46, ftell($handle));
        } else {
            $this->assertSame(42, ftell($handle));

            fread($handle, 42);
            $this->assertSame(84, ftell($handle));
        }

        fclose($handle);
    }
}
