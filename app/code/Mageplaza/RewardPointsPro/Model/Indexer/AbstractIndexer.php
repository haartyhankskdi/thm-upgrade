<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\Indexer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * Class AbstractIndexer
 * @package Mageplaza\RewardPointsPro\Model\Indexer
 */
abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var CacheContext
     */
    protected $cacheContext;

    /**
     * AbstractIndexer constructor.
     *
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     * @return void
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->getCacheManager()->clean($this->getIdentities());
    }

    /**
     * Get affected cache tags
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [
            Category::CACHE_TAG,
            Product::CACHE_TAG,
            Block::CACHE_TAG
        ];
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     *
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     *
     * @return void
     * @throws LocalizedException
     */
    abstract protected function doExecuteRow($id);

    /**
     * @return CacheInterface|mixed
     */
    private function getCacheManager()
    {
        if ($this->cacheManager === null) {
            $this->cacheManager = ObjectManager::getInstance()->get(CacheInterface::class);
        }

        return $this->cacheManager;
    }

    /**
     * Get cache context
     * @return CacheContext
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return ObjectManager::getInstance()->get(CacheContext::class);
        }

        return $this->cacheContext;
    }
}
