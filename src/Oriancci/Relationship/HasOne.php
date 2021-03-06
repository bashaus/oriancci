<?php

namespace Oriancci\Relationship;

class HasOne extends \Oriancci\Relationship
{

    public function result($resultsIn, $resultsOut)
    {
        foreach ($resultsIn as $resultIn) {
            foreach ($resultsOut as $resultOut) {
                if ($resultIn->{$this->fromField} == $resultOut->{$this->selectField}) {
                    $resultIn->relationshipSet($this->name, $resultOut);
                    break;
                }
            }
        }
    }
}
