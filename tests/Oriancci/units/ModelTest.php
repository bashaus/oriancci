<?php

namespace Oriancci;

use Oriancci\Models\User;

class ModelTest extends OriancciTest
{

    /* static - get */

    public function testAutoIncrement()
    {
        $user = User::select(1);
        $this->assertInstanceOf('Oriancci\Models\User', $user);
    }

    /* static - select */

    public function testSelectAll()
    {
        $users = User::select();
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertEquals(13, $users->count());
    }

    public function testSelectWhereNamed()
    {
        $selectUsers = User::select([WHERE => 'gender = :gender']);
        $users = $selectUsers->select([':gender' => 'M']);
        
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertNotEquals(0, $users->count());
    }

    public function testSelectWhereUnnamed()
    {
        $selectUsers = User::select([WHERE => 'gender = ?']);
        $users = $selectUsers->select(['M']);
        
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertNotEquals(0, $users->count());
    }

    /* static - count */

    public function testCount()
    {
        $this->assertEquals(13, User::count());
    }

    /* static - exists */

    public function testExists()
    {
        $exists = User::exists([WHERE => 'gender = ?'], ['F']);
        $this->assertTrue($exists);
    }

    public function testNotExists()
    {
        $exists = User::exists([WHERE => 'gender = ?'], ['X']);
        $this->assertFalse($exists);
    }

    /* static - select (one) */

    public function testSelectOneMethodExists()
    {
        $user = User::selectByFirstName('Adam');
        $this->assertInstanceOf('Oriancci\Models\User', $user[0]);
    }

    /* static - selectBy */

    public function testSelectByMethodConjunctionOr()
    {
        $users = User::selectByFirstNameOrLastName('Adam', 'Adam');
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertEquals(1, $users->count());
    }

    public function testSelectByMethodConjunctionAnd()
    {
        $users = User::selectByFirstNameAndLastName('Adam', 'Adam');
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertEquals(0, $users->count());
    }
    
    /* static - countBy */

    public function testCountBy()
    {
        $usersByGender = User::countByGender();
        $this->assertArrayHasKey('M', $usersByGender);
        $this->assertArrayHasKey('F', $usersByGender);

        $this->assertEquals(7, $usersByGender['M']);
        $this->assertEquals(6, $usersByGender['F']);
    }

    /* static - first */

    public function testFirst()
    {
        $user = User::first();
        $this->assertInstanceOf('Oriancci\Models\User', $user);
    }

    public function testFirstWithParameters()
    {
        $user = User::first([WHERE => 'gender = ?'], ['F']);
        $this->assertInstanceOf('Oriancci\Models\User', $user);
        $this->assertEquals('F', $user->gender);
    }

    public function testFirstNotFound()
    {
        $user = User::first([WHERE => 'gender = ?'], ['?']);
        $this->assertFalse($user);
    }

    /* static - last */

    public function testLast()
    {
        $user = User::last();
        $this->assertInstanceOf('Oriancci\Models\User', $user);
    }

    public function testLastWithParameters()
    {
        $user = User::last([WHERE => 'gender = ?'], ['F']);
        $this->assertInstanceOf('Oriancci\Models\User', $user);
        $this->assertEquals('F', $user->gender);
    }

    public function testLastNotFound()
    {
        $user = User::last([WHERE => 'gender = ?'], ['?']);
        $this->assertFalse($user);
    }

    /* instance - save */

    public function testSaveInsert()
    {
        $rows = $this->getConnection()->getRowCount(User::tableName());

        $user = new User;
        $user->firstName = 'Andrea';
        $user->lastName = 'Nelly';
        $user->email = 'andrea.nelly@example.com';
        $user->gender = 'F';
        $user->birthday = new \Oriancci\DataType\DateTime('1980-12-12');

        $this->assertTrue($user->save());
        $this->assertEquals($rows + 1, $this->getConnection()->getRowCount(User::tableName()));
    }

    public function testSaveUpdate()
    {
        $rows = $this->getConnection()->getRowCount(User::tableName());

        $user = User::select(1);
        $user->firstName = 'Alfred';

        $this->assertTrue($user->save());
        $this->assertEquals($rows, $this->getConnection()->getRowCount(User::tableName()));
    }

    public function testDelete()
    {
        $user = User::select(1);
        $this->assertTrue($user->delete());
        $this->assertEquals(0, $this->getConnection()->getRowCount('user', 'id = 1'));
    }

    public function testSaveAutoIncrement()
    {
        $user = new User([
            'firstName' => 'Jamie', 
            'lastName'  => 'Fino',
            'email'     => 'jamie.fino@example.com', 
            'gender'    => 'M',
            'birthday'  => '1982-10-01'
        ]);

        $this->assertTrue($user->save());
        $this->assertNotNull($user->autoIncrement());
    }

    /* instance - constructor */

    public function testConstructor()
    {
        $user = new User([
            'firstName' => 'Jamie', 
            'lastName'  => 'Fino',
            'email'     => 'em@il.com', 
            'gender'    => 'M',
            'birthday'  => '1982-10-01'
        ]);

        $this->assertEquals('Jamie'     , $user->firstName);
        $this->assertEquals('Fino'      , $user->lastName);
        $this->assertEquals('em@il.com' , $user->email);
        $this->assertEquals('M'         , $user->gender);
        $this->assertEquals('1982-10-01', $user->birthday->format('Y-m-d'));
    }

    /* instance - transaction */
    public function testTransactionPass()
    {
        $saved = User::transact(function() {
            $user = new User([
                'firstName' => 'Jamie', 
                'lastName'  => 'Fino',
                'email'     => 'em@il.com', 
                'gender'    => 'M',
                'birthday'  => '1982-10-01'
            ]);
            $user->save();

            return true;
        });

        $this->assertTrue($saved);
    }

    public function testTransactionFail()
    {
        $saved = User::transact(function() {
            $user = new User([
                'firstName' => 'Jamie', 
                'lastName'  => 'Fino',
                'email'     => 'em@il.com', 
                'gender'    => 'M',
                'birthday'  => '1982-10-01'
            ]);
            $user->save();

            return false;
        });

        $this->assertFalse($saved);
    }

    public function testTransactionException()
    {
        $saved = User::transact(function() {
            $user = new User([
                'firstName' => 'Jamie', 
                'lastName'  => 'Fino',
                'email'     => 'em@il.com', 
                'gender'    => 'M',
                'birthday'  => '1982-10-01'
            ]);
            $user->save();

            throw new \Exception('Test a failure');
        });

        $this->assertFalse($saved);
    }

    public function testJSON()
    {
        User::$serializable = array_fill_keys(
            array('firstName', 'email', 'gender', 'birthday'),
            true
        );

        $user = new User([
            'firstName' => 'Jane',
            'email'     => 'em@il.com',
            'gender'    => 'F'
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode((object) ['firstName' => 'Jane', 'email' => 'em@il.com', 'gender' => 'F', 'birthday' => null]),
            json_encode($user)
        );

        User::$serializable = null;
    }

    /* event target */

    public function testAfterConstruct()
    {
        $afterConstruct = false;
        $afterConstructFunc = function() use(&$afterConstruct) {
            $afterConstruct = true;
        };

        $afterInstantiation = false;
        $afterInstantiationFunc = function() use(&$afterInstantiation) {
            $afterInstantiation = true;
        };

        $afterSelection = false;
        $afterSelectionFunc = function() use(&$afterSelection) {
            $afterSelection = true;
        };

        User::eventAttach('afterConstruct', $afterConstructFunc);
        User::eventAttach('afterInstantiation', $afterInstantiationFunc);
        User::eventAttach('afterSelection', $afterSelectionFunc);

        $user = new User;
        $this->assertTrue($afterConstruct);
        $this->assertTrue($afterInstantiation);
        $this->assertFalse($afterSelection);

        User::eventDetach('afterConstruct', $afterConstructFunc);
        User::eventDetach('afterInstantiation', $afterInstantiationFunc);
        User::eventDetach('afterSelection', $afterSelectionFunc);

        $this->assertEquals(0, User::eventCount('afterConstruct'));
        $this->assertEquals(0, User::eventCount('afterInstantiation'));
        $this->assertEquals(0, User::eventCount('afterSelection'));
    }
}
