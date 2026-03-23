<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Adminhtml;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Storelocator\Model\ResourceModel\Schedule\Collection;

/**
 * Class Schedule
 */
abstract class Schedule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Storelocator::storelocator';

    /**
     * @var \Amasty\Storelocator\Model\Schedule
     */
    protected $scheduleModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionModel;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Collection
     */
    protected $scheduleCollection;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Storelocator\Model\Schedule $scheduleModel,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Base\Model\Serializer $serializer,
        Filter $filter,
        Collection $scheduleCollection,
        ?ForwardFactory $resultForwardFactory = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->scheduleModel = $scheduleModel;
        $this->logger = $logger;
        $this->sessionModel = $context->getSession();
        $this->serializer = $serializer;
        $this->filter = $filter;
        $this->scheduleCollection = $scheduleCollection;
        $this->resultForwardFactory = $resultForwardFactory
            ?? ObjectManager::getInstance()->get(ForwardFactory::class);
    }
}
