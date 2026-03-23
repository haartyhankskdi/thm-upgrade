<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UninstallCmsPagesData implements DataPatchInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepositoryInterface $pageRepository,
        ResourceInterface $moduleResource
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageRepository = $pageRepository;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): UninstallCmsPagesData
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_GdprCookie');
        if ($setupDataVersion && version_compare($setupDataVersion, '2.1.0', '<')) {
            $this->searchCriteriaBuilder->addFilter(
                PageInterface::IDENTIFIER,
                ['cookie-settings', 'cookie-policy'],
                'in'
            );
            $searchCriteria = $this->searchCriteriaBuilder->create();

            try {
                array_map(function ($cmsPage) {
                    $this->pageRepository->delete($cmsPage);
                }, $this->pageRepository->getList($searchCriteria)->getItems());
            } catch (LocalizedException $e) {
                null;
            }
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
