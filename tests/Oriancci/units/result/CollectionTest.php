<?php

namespace Oriancci;

use Oriancci\Models\User;

class CollectionTest extends OriancciTest
{

    public function testCollateString()
    {
        $users = User::select();
        $this->assertEquals(
            [
                1  => 'Adam',
                2  => 'Belinda',
                3  => 'Charlie',
                4  => 'Daniella',
                5  => 'Evan',
                6  => 'Francesca',
                7  => 'Gavin',
                8  => 'Harrison',
                9  => 'Ian',
                10 => 'Jesse',
                11 => 'Kevin',
                12 => 'Lea',
                13 => 'Matthew'
            ],
            $users->collate('id', 'firstName')
        );
    }

    public function testCollateArray()
    {
        $users = User::select();
        $this->assertEquals(
            [
                'M' => [
                    1  => 'Adam',
                    3  => 'Charlie',
                    5  => 'Evan',
                    7  => 'Gavin',
                    9  => 'Ian',
                    11 => 'Kevin',
                    13 => 'Matthew'
                ],
                'F' => [
                    2  => 'Belinda',
                    4  => 'Daniella',
                    6  => 'Francesca',
                    8  => 'Harrison',
                    10 => 'Jesse',
                    12 => 'Lea'
                ]
            ], 
            $users->collate(['gender', 'id'], 'firstName')
        );
    }

    public function testEach()
    {
        $users = User::select();
        $this->assertEquals(
            [
                'Adam', 'Belinda', 'Charlie', 'Daniella', 'Evan', 'Francesca', 
                'Gavin', 'Harrison', 'Ian', 'Jesse', 'Kevin', 'Lea', 'Matthew'
            ],
            $users->each('firstName')
        );
    }

    public function testIterator()
    {
        $users = User::select();
        $this->assertCount(13, $users);

        foreach ($users as $user) {
            $this->assertInstanceOf('Oriancci\Models\User', $user);
        }
    }
}
