<?php

namespace Kdi\CustomExport\Model\Source;

 

class Subscriber implements \Magento\Framework\Option\ArrayInterface

{

   public function toOptionArray()

   {

       $yesNoArray[] = [

           'label' => 'Subscribed',

           'value' => 1,

       ];

       $yesNoArray[] = [

           'label' => 'Not Activated',

           'value' => 2,

       ];

       $yesNoArray[] = [

           'label' => 'Unsubscribed',

           'value' => 3,

       ];

       $yesNoArray[] = [

           'label' => 'Unconfirmed',

           'value' => 4,

       ];

       return $yesNoArray;

   }

}