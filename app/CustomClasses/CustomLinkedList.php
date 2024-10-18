<?php

namespace App\CustomClasses;

use Smoren\Containers\Structs\LinkedList;

class CustomLinkedList extends LinkedList
{
    /**
     * @return mixed|null
     */
    public function peek()
    {
        // Check if the list is empty
        if ($this->last === null) {
            return null; // Return null if the list is empty
        }
        // Return the value of the last item
        return $this->last->getData();
    }

}

