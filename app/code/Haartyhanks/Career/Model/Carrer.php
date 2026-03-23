<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Model;

use Magento\Framework\Api\DataObjectHelper;
use Haartyhanks\Career\Api\Data\CarrerInterface;
use Haartyhanks\Career\Api\Data\CarrerInterfaceFactory;

class Carrer extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'haartyhanks_career_carrer';
    protected $dataObjectHelper;

    protected $carrerDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CarrerInterfaceFactory $carrerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Haartyhanks\Career\Model\ResourceModel\Carrer $resource
     * @param \Haartyhanks\Career\Model\ResourceModel\Carrer\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CarrerInterfaceFactory $carrerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Haartyhanks\Career\Model\ResourceModel\Carrer $resource,
        \Haartyhanks\Career\Model\ResourceModel\Carrer\Collection $resourceCollection,
        array $data = []
    ) {
        $this->carrerDataFactory = $carrerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve carrer model with carrer data
     * @return CarrerInterface
     */
    public function getDataModel()
    {
        $carrerData = $this->getData();
        
        $carrerDataObject = $this->carrerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $carrerDataObject,
            $carrerData,
            CarrerInterface::class
        );
        
        return $carrerDataObject;
    }
}

