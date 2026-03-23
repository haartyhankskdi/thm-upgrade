<?php

namespace Ebizmarts\BrippoPayments\Plugin\Email;

use Closure;
use Ebizmarts\BrippoPayments\Helper\SoftFailRecover;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Magento\Email\Model\Template\Config as TemplateConfig;
use UnexpectedValueException;

class TemplateIdConfigPlugin
{
    /**
     * Around plugin for getTemplateLabel method.
     *
     * @param TemplateConfig $subject
     * @param Closure $proceed
     * @param string $templateId
     * @return string
     */
    public function aroundGetTemplateLabel(
        TemplateConfig $subject,
        Closure $proceed,
        $templateId
    ) {
        if (strpos($templateId, 'recover_checkout_automatic_notifications_template') !== false) {
            try {
                $label = $proceed($templateId);
                if (!$label && $templateId !== RecoverCheckout::RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID) {
                    $label = $proceed(RecoverCheckout::RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID);
                }
            } catch (UnexpectedValueException $e) {
                if ($templateId !== RecoverCheckout::RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID) {
                    $label = $proceed(RecoverCheckout::RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID);
                } else {
                    throw $e;
                }
            }

            return $label;
        } else if (strpos($templateId, 'brippo_payments_paybylink') !== false) {
            try {
                $label = $proceed($templateId);
                if (!$label && $templateId !== PayByLink::DEFAULT_TEMPLATE_ID) {
                    $label = $proceed(PayByLink::DEFAULT_TEMPLATE_ID);
                }
            } catch (UnexpectedValueException $e) {
                if ($templateId !== PayByLink::DEFAULT_TEMPLATE_ID) {
                    $label = $proceed(PayByLink::DEFAULT_TEMPLATE_ID);
                } else {
                    throw $e;
                }
            }

            return $label;
        } else if (strpos($templateId, 'brippo_payments_soft_fail_recovery_soft_fail_recovery_email_template') !== false) {
            try {
                $label = $proceed($templateId);
                if (!$label && $templateId !== SoftFailRecover::BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID) {
                    $label = $proceed(SoftFailRecover::BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID);
                }
            } catch (UnexpectedValueException $e) {
                if ($templateId !== SoftFailRecover::BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID) {
                    $label = $proceed(SoftFailRecover::BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID);
                } else {
                    throw $e;
                }
            }

            return $label;
        }

        return $proceed($templateId);
    }
}
