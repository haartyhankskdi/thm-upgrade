<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Magewire\Checkout;

use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Helper\Calculation;
use Magewirephp\Magewire\Component;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Helper\Calculation as HelperCalculation;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

if (!class_exists(Component::class)) {
    class_alias(BaseComponent::class, 'Magewirephp\Magewire\Component');
}

/**
 * Class SpendPoints
 * @package Mageplaza\RewardPoints\Magewire\Checkout
 */
class SpendPoints extends Component
{
    protected $loader = true;

    /**
     * @var array
     */
    public array $points = [];

    /**
     * @var mixed
     */
    public $pointSpent = '0';

    /**
     * @var string|null
     */
    public ?string $ruleId = null;

    /**
     * @var SessionCheckout
     */
    protected $sessionCheckout;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * @var HelperCalculation
     */
    protected $helperCalculation;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;


    /**
     * @param SessionCheckout $sessionCheckout
     * @param HelperData $helperData
     * @param HelperCalculation $helperCalculation
     * @param CartTotalRepositoryInterface $cartTotalRepository
     */
    public function __construct(
        SessionCheckout $sessionCheckout,
        HelperData $helperData,
        HelperCalculation $helperCalculation,
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->sessionCheckout     = $sessionCheckout;
        $this->helperData          = $helperData;
        $this->helperCalculation   = $helperCalculation;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $this->initConfig();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function initConfig(): void
    {
        $quote        = $this->sessionCheckout->getQuote();
        $totals       = $this->cartTotalRepository->get($quote->getId());
        $this->points = Calculation::jsonDecode($totals->getExtensionAttributes()->getRewardPoints());
        $this->pointSpent = $quote->getMpRewardSpent() ?: '0';
    }

    /**
     * @param mixed $value
     * @param string|null $ruleId
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPointSpent($value, ?string $ruleId = null): void
    {
        $this->pointSpent = $value;
        $this->ruleId     = $ruleId;
        $this->updatedPointSpent($value);
    }

    /**
     * @param mixed $points
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updatedPointSpent($points): void
    {
        try {
            $points = (int) $points;
            $ruleId = $this->ruleId;
            $quote  = $this->sessionCheckout->getQuote();
            if (!$quote->getId()) {
                return;
            }
            $this->helperData->getCalculationHelper()->setQuote($quote);
            if ($ruleId === 'no_apply') {
                $points = 0;
            }
            $rate = $this->helperData->getCalculationHelper()->getSpendingRateByQuote($quote);
            if ($ruleId === 'rate' && $rate && $rate->getId()) {
                $minPoints = $rate->getMinPoint();
                $maxPoints = $this->helperData->getCalculationHelper()->getMaxSpendingPointsByRate($quote, $rate);

                if ($points < 0 || $points < $minPoints) {
                    $points = $minPoints;
                }

                if ($points > $maxPoints) {
                    $points = $maxPoints;
                }
            }
            $quote->setMpRewardSpent($points)->setMpRewardApplied($ruleId);

            if ($quote->getItemsCount() === 0) {
                throw new LocalizedException(
                    __('Totals calculation is not applicable to empty cart.')
                );
            }

            $quote->collectTotals()->save();
            $this->emit('payment_method_selected', ['method' => $quote->getPayment()->getMethod()]);
        } catch (LocalizedException $e) {
            $this->dispatchErrorMessage($e->getMessage());
        }
    }

    /**
     * Refresh component
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function refresh(): void
    {
        $this->initConfig();
    }
}
