<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Repository;

use Amasty\GdprCookie\Api\CookieRepositoryInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;
use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\EntityVersion\CookieVersionControlService;
use Amasty\GdprCookie\Model\EntityVersion\UpdateDataChecker;
use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CookieRepository implements CookieRepositoryInterface
{
    /**
     * @var CookieFactory
     */
    private $cookieFactory;

    /**
     * @var CookieResource
     */
    private $cookieResource;

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
    private $cookies;

    /**
     * Model data snapshot storage
     *
     * @var array
     */
    private $snapshots = [];

    public function __construct(
        CookieFactory $cookieFactory,
        CookieResource $cookieResource,
        CookieVersionControlService $cookieVersionControl,
        UpdateDataChecker $updateDataChecker
    ) {
        $this->cookieFactory = $cookieFactory;
        $this->cookieResource = $cookieResource;
        $this->cookieVersionControl = $cookieVersionControl;
        $this->updateDataChecker = $updateDataChecker;
    }

    /**
     * @inheritdoc
     */
    public function save(CookieInterface $cookie, int $storeId = 0)
    {
        try {
            if ($cookie->getId()) {
                $cookie = $this->getById($cookie->getId(), $storeId)
                    ->addData($cookie->getData());
            }

            // In case of creating new entity we MUST validate data changes and process validation callback
            $cookieSnapshot = $this->snapshots[$cookie->getId()][$storeId] ?? $this->cookieFactory->create();
            $this->cookieResource->setStoreId($storeId);
            $this->cookieResource->save($cookie);
            unset($this->cookies[$cookie->getId()], $this->snapshots[$cookie->getId()][$storeId]);
            $currentCookie = $this->getById($cookie->getId(), $storeId);

            if ($this->isDataChanged($cookieSnapshot, $currentCookie)) {
                $this->cookieVersionControl->updateVersion($storeId);
            }
        } catch (\Exception $e) {
            if ($cookie->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save cookie with ID %1. Error: %2',
                        [$cookie->getId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(__('Unable to save new cookie. Error: %1', $e->getMessage()));
        }

        return $cookie;
    }

    private function isDataChanged(
        CookieInterface $cookieSnapshot,
        CookieInterface $cookie
    ): bool {
        // This is a callback function that checks the visibility of changes on the frontend
        $callback = function (CookieInterface $cookieSnapshot, CookieInterface $cookie) {
            $isVisible = $cookieSnapshot->isEnabled() || $cookie->isEnabled();
            if ($isVisible) {
                $isVisible = $cookieSnapshot->getGroupId() || $cookie->getGroupId();
            }

            $isStatusChanged = $cookieSnapshot->isEnabled() != $cookie->isEnabled();
            $isGroupChanged = $cookieSnapshot->getGroupId() != $cookie->getGroupId();
            if ($isStatusChanged && $isGroupChanged
                && (($cookie->isEnabled() && !$cookie->getGroupId() && !$cookieSnapshot->isEnabled())
                    || !$cookie->isEnabled() && !$cookieSnapshot->getGroupId())
            ) {
                $isVisible = false;
            }

            return $isVisible;
        };

        return $this->updateDataChecker->execute($cookieSnapshot, $cookie, $callback);
    }

    /**
     * @inheritdoc
     */
    public function getById($cookieId, int $storeId = 0)
    {
        if (!isset($this->cookies[$cookieId][$storeId])) {
            /** @var \Amasty\GdprCookie\Model\Cookie $cookie */
            $cookie = $this->cookieFactory->create();
            $this->cookieResource->setStoreId($storeId);
            $this->cookieResource->load($cookie, $cookieId);

            if (!$cookie->getId()) {
                throw new NoSuchEntityException(__('Cookie with specified ID "%1" not found.', $cookieId));
            }
            $this->cookies[$cookieId][$storeId] = $cookie;
            $this->snapshots[$cookieId][$storeId] = clone $cookie;
        }

        return $this->cookies[$cookieId][$storeId];
    }

    /**
     * @inheritdoc
     */
    public function getByName($cookieName)
    {
        /** @var \Amasty\GdprCookie\Model\Cookie $cookie */
        $cookie = $this->cookieFactory->create();
        $this->cookieResource->load($cookie, $cookieName, CookieInterface::NAME);

        if (!$cookie->getId()) {
            throw new NoSuchEntityException(__('Cookie with specified Name "%1" not found.', $cookieName));
        }

        return $cookie;
    }

    /**
     * @inheritdoc
     */
    public function delete(CookieInterface $cookie)
    {
        try {
            $this->cookieResource->delete($cookie);
            if ($cookie->isEnabled() && $cookie->getGroupId()) {
                $this->cookieVersionControl->updateVersion();
            }
            unset($this->cookies[$cookie->getId()], $this->snapshots[$cookie->getId()]);
        } catch (\Exception $e) {
            if ($cookie->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove cookie with ID %1. Error: %2',
                        [$cookie->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove cookie. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($cookieId)
    {
        $cookie = $this->getById($cookieId);

        return $this->delete($cookie);
    }
}
