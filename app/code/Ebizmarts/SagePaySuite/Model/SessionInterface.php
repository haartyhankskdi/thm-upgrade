<?php

namespace Ebizmarts\SagePaySuite\Model;

interface SessionInterface
{
    public const PRESAVED_PENDING_ORDER_KEY = "sagepaysuite_presaved_order_pending_payment";
    public const CONVERTING_QUOTE_TO_ORDER = "sagepaysuite_converting_quote_to_order";
    public const CARD_IDENTIFIER = "sagepaysuite_card_identifier";
    public const MERCHANT_SESSION_KEY = "sagepaysuite_merchant_session_key";
    public const ACS_URL = "sagepaysuite_acs_url";
    public const MD = "3d_md";
    public const ORDER_IDS = "multishipping_order_ids";
}
