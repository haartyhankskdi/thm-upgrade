<?php

namespace Kdi\CustomExport\Model\Source;

 

class Prescriber implements \Magento\Framework\Option\ArrayInterface

{

   public function toOptionArray()

   {

       $yesNoArray[] = [

           'label' => 'Avi Test',

           'value' => 1,

       ];

       $yesNoArray[] = [

           'label' => 'Gurdev Sehmi',

           'value' => 2,

       ];

       $yesNoArray[] = [

           'label' => 'Bob Rihal',

           'value' => 3,

       ];

       $yesNoArray[] = [

           'label' => 'Vip Patel',

           'value' => 4,

       ];

       $yesNoArray[] = [

           'label' => 'Bez Hasanyan',

           'value' => 5,

       ];

       $yesNoArray[] = [

           'label' => 'OTC Sale',

           'value' => 6,

       ];

       $yesNoArray[] = [

           'label' => 'School Order',

           'value' => 7,

       ];

       $yesNoArray[] = [

           'label' => 'Vet Rx Needed',

           'value' => 8,

       ];

       $yesNoArray[] = [

           'label' => 'Amardeep Sehmi',

           'value' => 9,

       ];

       return $yesNoArray;

   }

}