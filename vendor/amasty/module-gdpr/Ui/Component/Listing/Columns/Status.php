<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Amasty\Gdpr\Model\Policy;

class Status extends Column
{
    public function prepare()
    {
        $data = $this->getData();
        $data['config']['editor']['options'] = [
            ['value' => Policy::STATUS_DISABLED, 'label' => __('Disabled')],
            ['value' => Policy::STATUS_ENABLED, 'label' => __('Enabled')]
        ];
        $this->setData($data);
        parent::prepare();
    }
}
