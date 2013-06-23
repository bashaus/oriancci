<?php

namespace Oriancci;

use Oriancci\Models\User;
use Oriancci\Models\Department;

class ModelBTREETest extends OriancciTest
{

    /* insertRoot *

    public function testInsertRoot()
    {
        $department = new Department;
        $department->name = 'Management';
        $this->assertTrue($department->insertRoot());
    }

    */

    public function testRoot()
    {
        $root = Department::root();
        $this->assertEquals(1, $root->left());
    }

    public function testAppendTo()
    {
        $department = new Department;
        $department->name = 'User experience';

        $root = Department::root();
        $this->assertTrue($department->appendTo($root));
    }

    public function testInsertBefore()
    {
        $department = new Department;
        $department->name = 'User experience';

        $before = Department::get(2);
        $this->assertTrue($department->insertBefore($before));
    }

    public function testInsertAfter()
    {
        $department = new Department;
        $department->name = 'User experience';

        $after = Department::get(2);
        $this->assertTrue($department->insertAfter($after));
    }

    public function testParents()
    {
        $department = new Department;
        $department->name = 'PHP';

        $backend = Department::get(3);
        $this->assertTrue($department->appendTo($backend));
        $this->assertCount(2, $department->parents());
    }

    public function testParent()
    {
        $backend = Department::get(3);
        $root = Department::root();

        $this->assertEquals($root->autoIncrement(), $backend->parent()->autoIncrement());
    }

    public function testCountDescendants()
    {
        $root = Department::root();
        $this->assertEquals(2, $root->countDescendants());
    }

    public function testDescendants()
    {
        $root = Department::root();
        $this->assertCount(2, $root->descendants());
    }

    public function testDelete()
    {
        $department = Department::get(3);
        $department->delete();
    }
}
