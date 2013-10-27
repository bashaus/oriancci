<?php

namespace Oriancci;

use Oriancci\Models\User;

class CursorTest extends OriancciTest
{

    public function testCursor()
    {
        $users = User::select([])
        	->setResultClass('\Oriancci\Result\Cursor')
        	->select();

        $this->assertInstanceOf('Oriancci\Result\Cursor', $users);
        $this->assertEquals(13, count($users));

        foreach ($users as $i => $user)
        {
        	$this->assertInstanceOf('Oriancci\Models\User', $user);
        }
    }
}
