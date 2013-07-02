<?php

namespace Oriancci\Relationship;

class HasMany extends \Oriancci\Relationship
{

    public function result($resultsIn, $resultsOut)
    {
        foreach ($resultsIn as $resultIn) {
            $relationshipArray = [];

            foreach ($resultsOut as $resultOut) {
                if ($resultIn->{$this->fromField} == $resultOut->{$this->selectField}) {
                    $relationshipArray[] = $resultOut;
                }
            }

            $resultIn->relationshipSet($this->name, $relationshipArray);
        }
    }
}
