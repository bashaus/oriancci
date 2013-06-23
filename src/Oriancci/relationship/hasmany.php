<?php

namespace Oriancci\Relationship;

class HasMany extends Relationship
{

    public function result($resultsIn, $resultsOut)
    {
        foreach ($resultsIn as $resultIn) {
            $relationshipArray = [];

            foreach ($resultsOut as $resultOut) {
                if ($resultIn->{$this->fromField} == $resultOut->{$this->findField}) {
                    $relationshipArray[] = $resultOut;
                }
            }

            $resultIn->relationshipSet($this->name, $relationshipArray);
        }
    }
}
