<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

interface ResultInterface
{
    public const SUCCESS       = 'success';
    public const ERROR_MESSAGE = 'error_message';
    public const RESPONSE      = 'response';
    public const REDIRECT_TO_FAILURE_URL = 'redirect_to_failure_url';

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return void
     */
    public function setSuccess($text);

    /**
     * @return string
     */
    public function getResponse();

    /**
     * @return void
     */
    public function setResponse($text);

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return void
     */
    public function setErrorMessage($text);

    /**
     * @return string
     */
    public function getRedirectToFailureUrl();

    /**
     * @param string $redirectToFailureUrl
     * @return void
     */
    public function setRedirectToFailureUrl($redirectToFailureUrl);
}
