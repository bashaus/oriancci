<?php

namespace Oriancci\Drivers\MySQL;

class Index extends \Oriancci\Index
{

    /**
     * The following come from the SQL query
     */

    public $Table;
    public $Non_unique;
    public $Key_name;
    public $Seq_in_index;
    public $Column_name;
    public $Collation;
    public $Cardinality;
    public $Sub_part;
    public $Packed;
    public $Null;
    public $Index_type;
    public $Comment;
}
