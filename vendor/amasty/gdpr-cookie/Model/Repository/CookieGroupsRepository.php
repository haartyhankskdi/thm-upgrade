<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Repository;

use Amasty\GdprCookie\Api\CookieGroupsRepositoryInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\Cookie\CookieBackend;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\EntityVersion\CookieVersionControlService;
use Amasty\GdprCookie\Model\EntityVersion\UpdateDataChecker;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CookieGroupsRepository implements CookieGroupsRepositoryInterface
{
    /**
     * @var CookieBackend
     */
    private $cookieBackend;

    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var CookieGroupResource
     */
    private $cookieGroupResource;

    /**
     * @var CookieVersionControlService
     */
    private $cookieVersionControl;

    /**
     * @var UpdateDataChecker
     */
    private $updateDataChecker;

    /**
     * Model data storage
     *
     * @var array
     */
    private $groups = [];

    /**
     * Model data snapshot storage
     *
     * @var array
     */
    private $snapshots = [];

    public function __construct(
        CookieBackend $cookieBackend,
        CookieGroupFactory $cookieGroupFactory,
        CookieGroupResource $cookieGroupResource,
        CookieVersionControlService $cookieVersionControl,
        UpdateDataChecker $updateDataChecker
    ) {
        $this->cookieBackend = $cookieBackend;
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->cookieGroupResource = $cookieGroupResource;
        $this->cookieVersionControl = $cookieVersionControl;
        $this->updateDataChecker = $updateDataChecker;
    }

    /**
     * @inheritdoc
     */
    public function save(CookieGroupsInterface $group, int $storeId = 0)
    {
        try {
            if ($group->getId()) {
                $group = $this->getById($group->getId(), $storeId)
                    ->addData($group->getData());
            }

            // In case of creating new entity we MUST validate data changes and process validation callback
            $groupSnapshot = $this->snapshots[$group->getId()][$storeId] ?? $this->cookieGroupFactory->create();
            $this->cookieGroupResource->setStoreId($storeId);
            $this->cookieGroupResource->save($group);
            unset($this->groups[$group->getId()], $this->snapshots[$group->getId()][$storeId]);
            $currentGroup = $this->getById($group->getId(), $storeId);

            if ($this->isDataChanged($groupSnapshot, $currentGroup)) {
                $this->cookieVersionControl->updateVersion($storeId);
            }
        } catch (\Exception $e) {
            if ($group->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save cookie group with ID %1. Error: %2',
                        [$group->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new cookie group. Error: %1', $e->getMessage()));
        }

        return $group;
    }

    private function isDataChanged(
        CookieGroupsInterface $groupSnapshot,
        CookieGroupsInterface $group
    ): bool {
        // This is a callback function that checks the visibility of changes on the frontend
        $callback = function (CookieGroupsInterface $groupSnapshot, CookieGroupsInterface $group) {
            return $groupSnapshot->isEnabled() || $group->isEnabled();
        };

        return $this->updateDataChecker->execute($groupSnapshot, $group, $callback);
    }

    /**
     * @inheritdoc
     */
    public function getById($groupId, int $storeId = 0)
    {
        if (!isset($this->groups[$groupId][$storeId])) {
            /** @var \Amasty\GdprCookie\Model\CookieGroup $group */
            $group = $this->cookieGroupFactory->create();
            $this->cookieGroupResource->setStoreId($storeId);
            $this->cookieGroupResource->load($group, $groupId);

            if (!$group->getId()) {
                throw new NoSuchEntityException(__('Cookie group with specified ID "%1" not found.', $groupId));
            }

            $group->setCookies(
                array_map(function ($cookie) {
                    return (int)$cookie->getId();
                }, $this->cookieBackend->getCookies($storeId, $groupId))
            );
            $this->groups[$groupId][$storeId] = $group;
            $this->snapshots[$groupId][$storeId] = clone $group;
        }

        return $this->groups[$groupId][$storeId];
    }

    /**
     * @inheritdoc
     */
    public function delete(CookieGroupsInterface $group)
    {
        try {
            $this->cookieGroupResource->delete($group);
            if ($group->isEnabled()) {
                $this->cookieVersionControl->updateVersion();
            }
            unset($this->groups[$group->getId()], $this->snapshots[$group->getId()]);
        } catch (\Exception $e) {
            if ($group->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove cookie group with ID %1. Error: %2',
                        [$group->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove cookie group. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($cookieId)
    {
        $group = $this->getById($cookieId);

        return $this->delete($group);
    }

    public function _resetState(): void
    {
        $this->groups = [];
    }
}
