<?php

namespace Oriancci\Models;

class User extends \Oriancci\Model
{

	static $validation = [
		'firstName' => ['required' => true],
		'lastName' 	=> ['required' => true],
		'email' 	=> ['email'    => true],
	];

    public static function tableName()
    {
        return 'user';
    }
}
