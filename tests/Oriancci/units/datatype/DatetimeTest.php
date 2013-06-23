<?php

namespace Oriancci;

class DatetimeTest extends OriancciTest
{

    public function testTimezone()
    {
        $date = new \Oriancci\DataType\DateTime(
            ['Y' => '1990', 'M' => '1', 'D' => '1', 'Z' => 'Australia/Brisbane']
        );

        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertEquals('Australia/Brisbane', $date->getTimezone()->getName());
        $this->assertFalse($date->isNull());

        $date = new \Oriancci\DataType\DateTime(
            ['Y' => '1990', 'M' => '1', 'D' => '1', 'Z' => 'Europe/London']
        );

        $this->assertEquals('Europe/London', $date->getTimezone()->getName());
        $this->assertFalse($date->isNull());
    }

    public function testSetTimezone()
    {
        // When you change the timezone, the time changes too
        $date = new \Oriancci\DataType\DateTime(
            ['Y' => '1990', 'M' => '1', 'D' => '1', 'H' => '23', 'I' => '59', 'Z' => 'UTC']
        );

        $date->setTimezone(new \DateTimeZone('Australia/Brisbane')); // UTC+10
        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('2'   , $date->format('d'));
        $this->assertEquals('Australia/Brisbane', $date->getTimezone()->getName());
        $this->assertEquals('Australia/Brisbane', $date->getInput('Z'));
        $this->assertFalse($date->isNull());

        $date = new \Oriancci\DataType\DateTime(
            ['Y' => '1990', 'M' => '1', 'D' => '1', 'Z' => 'Europe/London']
        );

        $this->assertEquals('Europe/London', $date->getTimezone()->getName());
        $this->assertFalse($date->isNull());
    }
    
    public function testDateFromArray()
    {
        $date = new \Oriancci\DataType\DateTime(['Y' => '1990', 'M' => '1', 'D' => '1']);
        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertFalse($date->isNull());
    }

    public function testInvalidDateFormatFromArray()
    {
        $date = new \Oriancci\DataType\DateTime(['Y' => 'INVALID', 'M' => 'INVALID']);
        $this->assertTrue($date->hasErrors());
        $this->assertEquals('INVALID', $date->getInput('Y'));
        $this->assertEquals('INVALID', $date->getInput('M'));
        $this->assertNull($date->getInput('D'));
    }

    public function testInvalidDateFromArray()
    {
        $date = new \Oriancci\DataType\DateTime(['Y' => '2000', 'M' => '02', 'D' => '31']);
        $this->assertTrue($date->hasErrors());
        $this->assertEquals('2000', $date->getInput('Y'));
        $this->assertEquals('02'  , $date->getInput('M'));
        $this->assertEquals('31'  , $date->getInput('D'));
    }

    public function testTimeFromArray()
    {
        $date = new \Oriancci\DataType\DateTime(['H' => '08', 'I' => '15']);
        $this->assertEquals('08', $date->format('H'));
        $this->assertEquals('15', $date->format('i'));
        $this->assertEquals('00', $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetDate()
    {
        $date = new \Oriancci\DataType\DateTime('1990-01-01 00:00:00');
        $date->setDate(1991, 02, 03);
        $this->assertEquals('1991', $date->format('Y'));
        $this->assertEquals('2'   , $date->format('m'));
        $this->assertEquals('3'   , $date->format('d'));
        $this->assertEquals('0'   , $date->format('H'));
        $this->assertEquals('0'   , $date->format('i'));
        $this->assertEquals('0'   , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetTime()
    {
        $date = new \Oriancci\DataType\DateTime('1990-01-01 00:00:00');
        $date->setTime(10, 11, 12);
        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertEquals('10'  , $date->format('H'));
        $this->assertEquals('11'  , $date->format('i'));
        $this->assertEquals('12'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testNull()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $this->assertNull($date->format('H'));
        $this->assertNull($date->getOffset());
        $this->assertNull($date->getTimestamp());
        $this->assertNull($date->getTimezone());
        $this->assertNull($date->modify('+1 day'));
        $this->assertEmpty($date->__toString());
        $this->assertTrue($date->isNull());
    }

    public function testNullSetTime()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $date->setTime(10, 11, 12);
        $this->assertEquals('10'  , $date->format('H'));
        $this->assertEquals('11'  , $date->format('i'));
        $this->assertEquals('12'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testNullSetDate()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $date->setDate(1990, 1, 1);
        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertEquals('00'  , $date->format('H'));
        $this->assertEquals('00'  , $date->format('i'));
        $this->assertEquals('00'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetTimestamp()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $this->assertTrue($date->isNull());

        $date->setTimestamp('631152000'); // 1990-01-01 00:00:00
        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertEquals('00'  , $date->format('H'));
        $this->assertEquals('00'  , $date->format('i'));
        $this->assertEquals('00'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetSub()
    {
        $date = new \Oriancci\DataType\DateTime('1990-01-02 00:00:00');
        $date->sub(new \DateInterval('P1D'));

        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('1'   , $date->format('d'));
        $this->assertEquals('00'  , $date->format('H'));
        $this->assertEquals('00'  , $date->format('i'));
        $this->assertEquals('00'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetSubNull()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $date->sub(new \DateInterval('P1D'));
        $this->assertTrue($date->isNull());
    }

    public function testSetAdd()
    {
        $date = new \Oriancci\DataType\DateTime('1990-01-01 00:00:00');
        $date->add(new \DateInterval('P1D'));

        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('1'   , $date->format('m'));
        $this->assertEquals('2'   , $date->format('d'));
        $this->assertEquals('00'  , $date->format('H'));
        $this->assertEquals('00'  , $date->format('i'));
        $this->assertEquals('00'  , $date->format('s'));
        $this->assertFalse($date->isNull());
    }

    public function testSetAddNull()
    {
        $date = new \Oriancci\DataType\DateTime(null);
        $date->add(new \DateInterval('P1D'));
        $this->assertTrue($date->isNull());
    }

    public function testSetDateIncomplete()
    {
        $date = new \Oriancci\DataType\DateTime(['Y' => '1990', 'M' => '05']);
        $this->assertTrue($date->hasErrors());
        $this->assertTrue($date->isNull());
        $this->assertEquals('1990', $date->getInput('Y'));
        $this->assertEquals('5'   , $date->getInput('M'));
        $this->assertNull($date->getInput('D'));

        $date->setInput(['D' => '06']);
        $this->assertFalse($date->hasErrors());
        $this->assertFalse($date->isNull());

        $this->assertEquals('1990', $date->format('Y'));
        $this->assertEquals('5'   , $date->format('m'));
        $this->assertEquals('6'   , $date->format('d'));

        $this->assertEquals('1990', $date->getInput('Y'));
        $this->assertEquals('5'   , $date->getInput('M'));
        $this->assertEquals('6'   , $date->getInput('D'));
    }

    public function testSetInputDate()
    {
        $date = new \Oriancci\DataType\DateTime;
        $date->setInput(['Y' => '1990', 'M' => '05', 'D' => '05']);

        $this->assertFalse($date->hasErrors());
        $this->assertFalse($date->isNull());
        $this->assertEquals('1990', $date->getInput('Y'));
        $this->assertEquals('5'   , $date->getInput('M'));
        $this->assertEquals('5'   , $date->getInput('D'));
        $this->assertNotNull($date->isNull('D'));
    }
}