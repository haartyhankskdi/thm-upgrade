<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Import\Validator;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    public const ERROR_INVALID_PHOTO = 'invalidPhoto';

    public const ERROR_NAME_IS_EMPTY = 'emptyName';

    public const ERROR_ID_IS_EMPTY = 'emptyId';

    public const ERROR_COUNTRY_IS_EMPTY = 'emptyCountry';
    
    public const ERROR_MEDIA_URL_NOT_ACCESSIBLE = 'cantGetPhoto';

    public const ENCODING_ERROR = 'encodingError';

    public const ERROR_GOOGLE_GEO_DATA = 'geoDataError';

    public const API_STATUS_ZERO_RESULTS = 'ZERO_RESULTS';

    public const API_STATUS_OVER_DAILY_LIMIT = 'OVER_DAILY_LIMIT';

    public const API_STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';

    public const API_STATUS_REQUEST_DENIED = 'REQUEST_DENIED';

    public const API_STATUS_INVALID_REQUEST = 'INVALID_REQUEST';

    public const API_STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    /**
     * Initialize validator
     *
     * @param $context
     * @return $this
     */
    public function init($context);
}
