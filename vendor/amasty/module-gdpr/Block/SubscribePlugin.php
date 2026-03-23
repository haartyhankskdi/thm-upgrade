<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Newsletter\Block\Subscribe as SubscribeBlock;
use Magento\Framework\Exception\LocalizedException;

class SubscribePlugin
{
    private const CHECKBOX_BLOCK_PREFIX = 'amasty_gdpr_newsletter';

    /**
     * @param SubscribeBlock $subject
     * @param $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(SubscribeBlock $subject, $result)
    {
        $layout = $subject->getLayout();
        $name = self::CHECKBOX_BLOCK_PREFIX . '.' . $subject->getNameInLayout();

        if (!$this->isNewsletterBlockExist($layout)
            || $layout->hasElement($name)
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            \Amasty\Gdpr\Block\Checkbox::class,
            $name,
            [
                'scope' => ConsentLogger::FROM_SUBSCRIPTION
            ]
        )->setTemplate('Amasty_Gdpr::checkbox.phtml')->toHtml();

        if ($checkboxBlock) {
            $pos = strripos($result, '</form>');

            if ($pos) {
                $endOfHtml = substr($result, $pos);
                $result = substr_replace($result, $checkboxBlock, $pos) . $endOfHtml;
            }
        }

        return $result;
    }

    private function isNewsletterBlockExist($layout): bool
    {
        return $layout->hasElement('form.subscribe')
            || $layout->hasElement('footer.form.subscribe');
    }
}
