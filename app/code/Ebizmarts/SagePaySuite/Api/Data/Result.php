<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;

class Result extends AbstractExtensibleModel implements ResultInterface
{
    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->getData(self::SUCCESS);
    }

    /**
     * @return void
     */
    public function setSuccess($text)
    {
        $this->setData(self::SUCCESS, $text);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @return void
     */
    public function setResponse($text)
    {
        $this->setData(self::RESPONSE, $text);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getData(self::ERROR_MESSAGE);
    }

    /**
     * @return void
     */
    public function setErrorMessage($text)
    {
        $this->setData(self::ERROR_MESSAGE, $text);
    }

    /**
     * {@inheritDoc}
     */
    public function getRedirectToFailureUrl()
    {
        return $this->getData(self::REDIRECT_TO_FAILURE_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function setRedirectToFailureUrl($redirectToFailureUrl)
    {
        $this->setData(self::REDIRECT_TO_FAILURE_URL, $redirectToFailureUrl);
    }
    public function __toArray(): array
    {
        return [
            self::SUCCESS => $this->getSuccess(),
            self::RESPONSE => $this->getResponse(),
            self::ERROR_MESSAGE => $this->getErrorMessage(),
            self::REDIRECT_TO_FAILURE_URL => $this->getRedirectToFailureUrl()
        ];
    }
}
