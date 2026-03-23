<?php

namespace Kdi\CustomExport\Model\Source;

 

class OrderStatus implements \Magento\Framework\Option\ArrayInterface

{

   public function toOptionArray()

   {

       $yesNoArray[] = [

           'label' => 'Approved',

           'value' => 'approve',

       ];

       $yesNoArray[] = [

           'label' => 'Cancelled',

           'value' => 'canceled',

       ];

       $yesNoArray[] = [

           'label' => 'Closed',

           'value' => 'closed',

       ];

       $yesNoArray[] = [

           'label' => 'Complete',

           'value' => 'complete',

       ];

       $yesNoArray[] = [

           'label' => 'Disapproved',

           'value' => 'disapprove',

       ];

       $yesNoArray[] = [

           'label' => 'Suspected Fraud',

           'value' => 'fraud',

       ];

       $yesNoArray[] = [

           'label' => 'On Hold',

           'value' => 'holded',

       ];

       $yesNoArray[] = [

           'label' => 'Payment Review',

           'value' => 'payment_review',

       ];

       $yesNoArray[] = [

           'label' => 'PayPal Canceled Reversal',

           'value' => 'paypal_canceled_reversal',

       ];

       $yesNoArray[] = [

           'label' => 'PayPal Reversed',

           'value' => 'paypal_reversed',

       ];

       $yesNoArray[] = [

           'label' => 'Pending',

           'value' => 'pending',

       ];

       $yesNoArray[] = [

           'label' => 'Pending Payment',

           'value' => 'pending_payment',

       ];

       $yesNoArray[] = [

           'label' => 'Pending PayPal',

           'value' => 'pending_paypal',

       ];

       $yesNoArray[] = [

           'label' => 'Prescriber Query',

           'value' => 'prescriber_query',

       ];

       $yesNoArray[] = [

           'label' => 'Processing',

           'value' => 'processing',

       ];

       $yesNoArray[] = [

           'label' => 'Sage Pay Canceled',

           'value' => 'sagepaysuite_pending_cancel',

       ];

       $yesNoArray[] = [

           'label' => 'Sage Pay Pending Payment',

           'value' => 'sagepaysuite_pending_payment',

       ];

       return $yesNoArray;

   }

}