<?php

namespace Oriancci\Models;

class User extends \Oriancci\Model
{

    public function validate($filter)
    {
        $filter->addSoftRule('firstName', $filter::IS, 'alpha');
        $filter->addSoftRule('lastName' , $filter::IS, 'alpha');
        $filter->addSoftRule('email'    , $filter::IS, 'email');
    }

    public static function tableName()
    {
        return 'user';
    }
}
