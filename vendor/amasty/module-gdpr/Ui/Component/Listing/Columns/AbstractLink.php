<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

abstract class AbstractLink extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[$this->getIdFieldName()] == 0) {
                    continue;
                }
                $url = $this->context->getUrl(
                    $this->getUrl(),
                    [$this->getIdParamName() => $item[$this->getIdFieldName()]]
                );
                $item[$this->_data['config']['link']] = $url;
            }
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['link'] = $this->_data['name'] . '_link';
    }

    abstract protected function getIdFieldName(): string;

    abstract protected function getIdParamName(): string;

    abstract protected function getUrl(): string;
}
