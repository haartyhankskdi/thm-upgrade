<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;

class Mail extends AbstractHelper
{
    const XML_SENDER_EMAIL          = 'general_section/general_group/email_identity';
    const XML_SENDER_EMAIL_TO       = 'general_section/general_group/email_to';
    const XML_SENDER_EMAIL_CC       = 'general_section/general_group/email_cc';
    const XML_SENDER_EMAIL_ENABLE   = 'general_section/general_group/enable';

    protected $transportBuilder;
    protected $storeManager;
    protected $senderResolver;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SenderResolverInterface $senderResolver
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->senderResolver = $senderResolver;
        parent::__construct($context);
    }

    /**
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $to email and name of the receiver
     * @param array $templateParams
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
protected function sendEmailTemplate(
    $template,
    $to,
    $fromArray = [],
    $add_cc = [],
    $templateParams = [],
    $storeId = null
)
{
        if (!isset($to) || empty($to)) {
            throw new LocalizedException(
                __('We could not send the email because the receiver data is invalid.')
            );
        }
        $storeId = $storeId ? $storeId : $this->storeManager->getStore()->getId();
        $name = isset($fromArray['name']) ? $fromArray['name'] : '';
         
        $from = [
            'name' => $name,
            'email' => $fromArray,
        ];

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $from
        )->addTo(
            $to
        )->addCc(
            $add_cc
        )->getTransport();
        return $transport->sendMessage();
    }

    /**
     * Send the EmailTemplate Email
     */
    public function sendAdminCareerEmail(
        $templateParams = [],
        $to = ''
    ) {
        $from = $this->getSenderEmail(self::XML_SENDER_EMAIL);
        $add_cc = $this->getCcArray(self::XML_SENDER_EMAIL_CC);
        $isAllow = $this->coreConfig(self::XML_SENDER_EMAIL_ENABLE);
        if(empty($to)){
            $to = $this->coreConfig(self::XML_SENDER_EMAIL_TO);
        }
        // print_r($add_cc);
        // print_r($to);
        // print_r($templateParams);
        // exit();

        if($isAllow){
            // throw new LocalizedException(
            //     json_encode($from)
            // );
            return $this->sendEmailTemplate(
                'general_section/general_group/email_template',
                $from,
                $to,
                $add_cc,
                $templateParams
            );
        }
    }

    public function getSenderEmail( $xmlPath = "sales_email/order/identity" )
    {
        return $this->senderResolver->resolve($this->scopeConfig->getValue(
            $xmlPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getCcArray(String $xmlPath = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if($xmlPath != null){
            $emailComma = $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE, $storeId);
            if (!empty($emailComma)) {
                return (array) \explode(",", $emailComma);
            }
        }
        return [];
    }

    public function coreConfig(String $xmlPath = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if($xmlPath != null){
            return $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE, $storeId);
        }
        return 0;
    }
}
