<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Contact\Block\ContactForm as ContactBlock;
use Magento\Framework\Exception\LocalizedException;

class ContactPlugin
{
    /**
     * @param ContactBlock $subject
     * @param               $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(ContactBlock $subject, $result)
    {
        $layout = $subject->getLayout();

        if (!$layout->getBlock('contactForm')
            || $layout->getBlock('amasty_gdpr_contact')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            \Amasty\Gdpr\Block\Checkbox::class,
            'amasty_gdpr_contact',
            [
                'scope' => ConsentLogger::FROM_CONTACTUS
            ]
        )->setTemplate('Amasty_Gdpr::checkbox.phtml')->toHtml();

        if ($checkboxBlock) {
            $fieldsetText = '</fieldset>';
            $pos = strripos($result, $fieldsetText);
            if ($pos !== false) {
                $result = substr_replace($result, $checkboxBlock . $fieldsetText, $pos, strlen($fieldsetText));
            }
        }

        return $result;
    }
}
