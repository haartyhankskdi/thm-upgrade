<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Repository;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Amasty\GdprCookie\Api\CookieConsentRepositoryInterface;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent\Collection;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent\CollectionFactory;
use Amasty\GdprCookie\Model\CookieConsentFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CookieConsentRepository implements CookieConsentRepositoryInterface
{
    /**
     * @var CookieConsentFactory
     */
    private $cookieConsentFactory;

    /**
     * @var CookieConsentResource
     */
    private $cookieConsentResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $cookieConsents;

    /**
     * @var CollectionFactory
     */
    private $cookieConsentCollectionFactory;

    public function __construct(
        CookieConsentFactory $cookieConsentFactory,
        CookieConsentResource $cookieConsentResource,
        CollectionFactory $cookieConsentCollectionFactory
    ) {
        $this->cookieConsentFactory = $cookieConsentFactory;
        $this->cookieConsentResource = $cookieConsentResource;
        $this->cookieConsentCollectionFactory = $cookieConsentCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CookieConsentInterface $cookieConsent)
    {
        try {
            if ($cookieConsent->getId()) {
                $cookieConsent = $this->getById($cookieConsent->getId())
                    ->addData($cookieConsent->getData());
            }
            $this->cookieConsentResource->save($cookieConsent);
            unset($this->cookieConsents[$cookieConsent->getId()]);
        } catch (\Exception $e) {
            if ($cookieConsent->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save cookie consent with ID %1. Error: %2',
                        [$cookieConsent->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new withConsent. Error: %1', $e->getMessage()));
        }

        return $cookieConsent;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->cookieConsents[$id])) {
            /** @var \Amasty\GdprCookie\Model\cookieConsent $cookieConsent */
            $cookieConsent = $this->cookieConsentFactory->create();
            $this->cookieConsentResource->load($cookieConsent, $id);
            if (!$cookieConsent->getId()) {
                throw new NoSuchEntityException(__('Cookie Consent with specified ID "%1" not found.', $id));
            }
            $this->cookieConsents[$id] = $cookieConsent;
        }

        return $this->cookieConsents[$id];
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $cookieConsentModel = $this->getById($id);

        return $this->delete($cookieConsentModel);
    }

    /**
     * @inheritdoc
     */
    public function delete(CookieConsentInterface $cookieConsent)
    {
        try {
            $this->cookieConsentResource->delete($cookieConsent);
            unset($this->cookieConsents[$cookieConsent->getId()]);
        } catch (\Exception $e) {
            if ($cookieConsent->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Cookie Consent with ID %1. Error: %2',
                        [$cookieConsent->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Cookie Consent. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId($id)
    {
        // Skip collection filtering for guest customer.
        if ($id === 0) {
            return false;
        }

        /** @var Collection $cookieConsentCollection */
        $cookieConsentCollection = $this->cookieConsentCollectionFactory->create();

        /** @var CookieConsentInterface $cookieConsent */
        $cookieConsent = $cookieConsentCollection
            ->addFieldToFilter('customer_id', $id)
            ->getFirstItem();

        return $cookieConsent ? : false;
    }

    public function _resetState(): void
    {
        $this->cookieConsents = [];
    }
}
