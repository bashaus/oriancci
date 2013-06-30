<?php

namespace Oriancci\DataType;

interface DataTypeInterface
{

    public function resetErrors();
    public function getErrors();
    public function hasErrors();
    public function isNull();
    public function toDB();
}
