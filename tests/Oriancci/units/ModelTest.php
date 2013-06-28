<?php

namespace Oriancci;

use Oriancci\Models\User;

class ModelTest extends OriancciTest
{

    /* static - get */

    public function testGetByPrimaryKey()
    {
        $user = User::get(1);
        $this->assertInstanceOf('Oriancci\Models\User', $user);
    }

    /* static - find */

    public function testFindAll()
    {
        $users = User::find();
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertEquals(13, $users->count());
    }

    public function testFindWhere()
    {
        $findUsers = User::find([WHERE => 'gender = :gender']);
        $users = $findUsers->select([':gender' => 'M']);
        
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertNotEquals(0, $users->count());
    }

    /* static - count */

    public function testCount()
    {
        $this->assertEquals(13, User::count());
    }

    /* static - getBy */

    public function testGetByMethodExists()
    {
        $user = User::getByFirstName('Adam');
        $this->assertInstanceOf('Oriancci\Models\User', $user);
    }

    public function testGetByMethodDoesntExist()
    {
        $user = User::getByFirstName('Ben');
        $this->assertFalse($user);
    }

    /* static - findBy */

    public function testFindByMethodConjunctionOr()
    {
        $users = User::findByFirstNameOrLastName('Adam', 'Adam');
        $this->assertInstanceOf('Oriancci\Result\Collection', $users);
        $this->assertEquals(1, $users->count());
    }

    public function testFindByMethodConjunctionAnd()
    {
        $users = User::findByFirstNameAndLastName('Adam', 'Adam');
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

        $user = User::get(1);
        $user->firstName = 'Alfred';

        $this->assertTrue($user->save());
        $this->assertEquals($rows, $this->getConnection()->getRowCount(User::tableName()));
    }

    public function testSaveAutoIncrement()
    {
        $user = new User([
            'firstName' => 'Jamie', 
            'lastName'  => 'Fino',
            'email'     => 'jamie.fino@example.com', 
            'gender'    => 'M',
            'birthday'  => new \Oriancci\Datatype\Datetime('1982-10-01')
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
        $saved = Department::transact(function() use($name) {
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
        $saved = Department::transact(function() use($name) {
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
        $saved = Department::transact(function() use($name) {
            $user = new User([
                'firstName' => 'Jamie', 
                'lastName'  => 'Fino',
                'email'     => 'em@il.com', 
                'gender'    => 'M',
                'birthday'  => '1982-10-01'
            ]);
            $user->save();

            throw new Exception('Test a failure');
        });

        $this->assertFalse($saved);
    }
}
