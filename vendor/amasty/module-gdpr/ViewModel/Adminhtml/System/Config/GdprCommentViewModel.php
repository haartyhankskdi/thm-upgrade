<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\ViewModel\Adminhtml\System\Config;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class GdprCommentViewModel implements ArgumentInterface
{
    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $sectionComments = [];

    /**
     * @var string|null
     */
    private $sectionName;

    public function __construct(
        UrlInterface $backendUrl,
        RequestInterface $request,
        ?array $sectionComments = null
    ) {
        $this->backendUrl = $backendUrl;
        $this->request = $request;
        $this->sectionComments = $sectionComments;
        $this->sectionName = $this->request->getParam('section', null);
    }

    public function getTranslatedComment(): string
    {
        if (!$this->isValidSection()) {
            return '';
        }

        return str_replace(
            '%1',
            $this->backendUrl->getUrl('amasty_gdpr/consents/index'),
            $this->sectionComments[$this->sectionName]['comment']
        );
    }

    public function getSelector(): string
    {
        if (!$this->isValidSection()) {
            return '';
        }

        return $this->sectionComments[$this->sectionName]['selector'];
    }

    public function isValidSection(): bool
    {
        return $this->sectionName && isset($this->sectionComments[$this->sectionName]);
    }
}
