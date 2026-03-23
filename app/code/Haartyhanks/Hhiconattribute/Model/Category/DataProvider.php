<?php
namespace Haartyhanks\Hhiconattribute\Model\Category;
 
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
 
	protected function getFieldsMap()
	{
    	$fields = parent::getFieldsMap();
        $fields['content'][] = 'APP_Category_Icon'; // custom image field
    	
    	return $fields;
	}
}