<?php

namespace Oriancci\Relationship;

class HasOne extends Relationship
{

    public function result($resultsIn, $resultsOut)
    {
        foreach ($resultsIn as $resultIn) {
            foreach ($resultsOut as $resultOut) {
                if ($resultIn->{$this->fromField} == $resultOut->{$this->findField}) {
                    $resultIn->relationshipSet($this->name, $resultOut);
                    break;
                }
            }
        }
    }
}
