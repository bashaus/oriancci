<?php

namespace Oriancci\Traits;

use Oriancci\Models\User;
use PHPUnit_Framework_TestCase;

class EventTargetTest extends PHPUnit_Framework_TestCase
{

    public function testAttachDetach()
    {
        $afterConstructFunc = function() {};
        $afterInstantiationFunc = function() {};
        $afterSelectionFunc = function() {};

        User::eventAttach('afterConstruct', $afterConstructFunc);
        User::eventAttach('afterInstantiation', $afterInstantiationFunc);
        User::eventAttach('afterSelection', $afterSelectionFunc);

        $this->assertEquals(1, User::eventCount('afterConstruct'));
        $this->assertEquals(1, User::eventCount('afterInstantiation'));
        $this->assertEquals(1, User::eventCount('afterSelection'));

        User::eventDetach('afterConstruct', $afterConstructFunc);
        User::eventDetach('afterInstantiation', $afterInstantiationFunc);
        User::eventDetach('afterSelection', $afterSelectionFunc);

        $this->assertEquals(0, User::eventCount('afterConstruct'));
        $this->assertEquals(0, User::eventCount('afterInstantiation'));
        $this->assertEquals(0, User::eventCount('afterSelection'));
    }

    public function testEventCount()
    {
        $afterConstructFunc = function() {};
        
        User::eventAttach('afterConstruct', $afterConstructFunc);
        $this->assertEquals(1, User::eventCount('afterConstruct'));

        User::eventDetach('afterConstruct', $afterConstructFunc);
        $this->assertEquals(0, User::eventCount('afterConstruct'));

        $this->assertEquals(0, User::eventCount('nonExistentEvent'));
    }
    public function testDetachNonExistent()
    {
        $afterConstructFunc = function() {};

        User::eventAttach('afterConstruct', $afterConstructFunc);
        $this->assertEquals(1, User::eventCount('afterConstruct'));

        User::eventDetach('afterConstruct', $afterConstructFunc);
        $this->assertEquals(0, User::eventCount('afterConstruct'));

        // Already detached
        $this->assertFalse(User::eventDetach('afterConstruct', $afterConstructFunc));

        // Non existent event
        $this->assertFalse(User::eventDetach('nonExistentEvent', $afterConstructFunc));
    }
}