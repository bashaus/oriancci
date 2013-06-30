<?php

namespace Oriancci\Models;

class Department extends \Oriancci\Model\BTree
{

	public static function databaseName()
	{
		return ORIANCCI_PHPUNIT_DATABASE;
	}

    public static function tableName()
    {
        return 'department';
    }
}
