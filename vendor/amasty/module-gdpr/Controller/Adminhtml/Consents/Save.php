<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Consents;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsents;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\Source\CountriesRestrictment;
use Amasty\Gdpr\Model\Source\LinkToPolicy;
use Amasty\Gdpr\Ui\DataProvider\Form\ConsentsDataProvider;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use \Magento\Cms\Helper\Page as CmsHelper;

class Save extends AbstractConsents
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CmsHelper
     */
    private $cmsHelper;

    public function __construct(
        Repository $repository,
        Context $context,
        LoggerInterface $logger,
        CmsHelper $cmsHelper
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->cmsHelper = $cmsHelper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            if ($data = $this->getRequest()->getPostValue(ConsentsDataProvider::CONSENT_SCOPE)) {
                $storeId = isset($data[Store::STORE_ID]) ? (int)$data[Store::STORE_ID] : Store::DEFAULT_STORE_ID;
                $consentId = isset($data[Consent::ID]) ? (int)$data[Consent::ID] : null;
                $consentCode = isset($data[Consent::CONSENT_CODE]) ? (string)$data[Consent::CONSENT_CODE] : null;
                $model = $this->getConsentModel($consentId, $storeId, $consentCode);
                $this->prepareRawData($data);
                $model->addData($data);
                $model->getStoreModel()->addData($data);
                $this->repository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the consent.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        '_current' => true,
                        'store' => $storeId
                    ]);
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->processError($e);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($e);

            return $this->processError($e);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*');
    }

    /**
     * @param string|null $consentCode
     * @param int $storeId
     *
     * @return bool
     */
    private function isConsentCodeUnique($consentCode, int $storeId)
    {
        if ($consentCode && $storeId === Store::DEFAULT_STORE_ID) {
            try {
                $this->repository->getByConsentCode($consentCode);

                return false;
            } catch (NoSuchEntityException $e) {
                return true;
            }
        }

        return true;
    }

    /**
     * @param \Exception $e
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function processError(\Exception $e)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        } else {
            $resultRedirect->setPath('*/*/new');
        }

        return $resultRedirect;
    }

    /**
     * @param int|null $consentId
     * @param int $storeId
     *
     * @param string|null $consentCode
     *
     * @return Consent
     * @throws CouldNotSaveException
     */
    private function getConsentModel($consentId, int $storeId, $consentCode)
    {
        if ($consentId) {
            try {
                $model = $this->repository->getById($consentId, $storeId);

                if ($storeId !== Store::DEFAULT_STORE_ID) {
                    $model->setStoreModel($this->repository->getEmptyConsentStoreModel());
                }
            } catch (NoSuchEntityException $e) {
                $model = $this->repository->getEmptyConsentModel();
            }
        } else {
            if ($this->isConsentCodeUnique($consentCode, $storeId)) {
                $model = $this->repository->getEmptyConsentModel();
            } else {
                throw new CouldNotSaveException(__('Consent with code %1 already exists.', $consentCode));
            }
        }

        return $model;
    }

    /**
     * @param array $data
     */
    private function prepareRawData(array &$data)
    {
        $this->prepareSpecifiedCountries($data);
        $this->prepareConsentLocations($data);
        $this->prepareLinkToPolicy($data);
    }

    /**
     * @param array $data
     */
    private function prepareSpecifiedCountries(array &$data)
    {
        $countries = $data[ConsentStore::COUNTRIES] ?? null;

        if (is_array($countries)) {
            $visibility = (int)($data[ConsentStore::VISIBILITY] ?? CountriesRestrictment::ALL_COUNTRIES);

            if ($visibility !== CountriesRestrictment::SPECIFIED_COUNTRIES) {
                $data[ConsentStore::COUNTRIES] = '';
            }

            if ($visibility === CountriesRestrictment::SPECIFIED_COUNTRIES) {
                $data[ConsentStore::COUNTRIES] = implode(',', $countries);
            }
        }
    }

    /**
     * @param array $data
     */
    private function prepareConsentLocations(array &$data)
    {
        $locations = $data[ConsentStore::CONSENT_LOCATION] ?? null;

        if (is_array($locations)) {
            //if enabled social login location - enable default registration
            if (in_array('amsociallogin_popup_form', $locations)
                && !in_array(ConsentLogger::FROM_REGISTRATION, $locations)
            ) {
                $locations[] = ConsentLogger::FROM_REGISTRATION;
            }

            $data[ConsentStore::CONSENT_LOCATION] = implode(',', $locations);
        }
    }

    /**
     * @param array $data
     */
    private function prepareLinkToPolicy(array &$data)
    {
        $privacyPolicyLinkType = $data[ConsentStore::LINK_TYPE] ?? ConsentLinkType::PRIVACY_POLICY;

        if ($privacyPolicyLinkType === ConsentLinkType::CMS_PAGE) {
            $data[ConsentStore::CMS_PAGE_ID] = null;
        }
    }
}
