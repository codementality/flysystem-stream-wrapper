<?php

use Lhridley\Uid;
use PHPUnit\Framework\TestCase;

class UidTest extends TestCase {
    public function test()
    {
        $uid = new Uid();

        $info = stat(__FILE__);

        $this->assertSame($info['uid'], $uid->getUid());
        $this->assertSame($info['gid'], $uid->getGid());
    }
}
