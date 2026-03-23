<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kdi\Category\ViewModel\Category;

class View extends \Magento\Framework\DataObject implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * View constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHeroImage()
    {
        //Your viewModel code
        // you can use me in your template like:
        // $viewModel = $block->getData('viewModel');
        // echo $viewModel->getHeroImage();
        
        return __('Hello Developer!');
    }
}

