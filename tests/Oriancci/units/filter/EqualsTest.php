<?php

namespace Oriancci;

use Oriancci\Models\User;

class EqualsTest extends OriancciTest
{
    
    public function testFilter()
    {
        $users = User::find();
        $this->assertCount(13, $users);

        foreach ($users as $user) {
            $this->assertInstanceOf('Oriancci\Models\User', $user);
        }

        //

        $males = new Filter\Equals($users, 'gender', 'M');
        $females = new Filter\Equals($users, 'gender', 'F');

        foreach ($males as $male) {
            $this->assertEquals('M', $male->gender);
        }

        foreach ($females as $female) {
            $this->assertEquals('F', $female->gender);
        }
    }
}