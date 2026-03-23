<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\MollieThemeBundle\Plugin;

use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier;

class MovePaymentMethods
{
    private const RECOMMENDED_SOLUTIONS = 'recommended_solutions';
    private const OTHER_PAYMENT_METHODS = 'other_payment_methods';
    private const OTHER_PAYPAL_PAYMENT_SOLUTIONS = 'other_paypal_payment_solutions';
    private const CHILDREN = 'children';
    private const RECOMMENDED_SOLUTIONS_ALLOWED_LIST = ['mollie', 'mollie_payments', 'magento_payments', 'magento_payments_legacy'];
    private const MOLLIE_PAYMENT_METHODS = ['mollie_payments'];

    /**
     * Move Mollie to recommended solutions section.
     *
     * @param PaymentSectionModifier $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function afterModify(PaymentSectionModifier $subject, $result)
    {
        $result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN] = $result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN] ?? [];
        $result[self::OTHER_PAYMENT_METHODS][self::CHILDREN] = $result[self::OTHER_PAYMENT_METHODS][self::CHILDREN] ?? [];
        $result[self::OTHER_PAYPAL_PAYMENT_SOLUTIONS][self::CHILDREN] = $result[self::OTHER_PAYPAL_PAYMENT_SOLUTIONS][self::CHILDREN] ?? [];

        foreach (array_keys($result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN]) as $key) {
            if (!in_array($key, self::RECOMMENDED_SOLUTIONS_ALLOWED_LIST, true)) {
                $result = $this->moveMethodToOtherPaymentMethods($result, $key);
            }
        }

        foreach ([self::OTHER_PAYMENT_METHODS, self::OTHER_PAYPAL_PAYMENT_SOLUTIONS] as $group) {
            foreach (array_keys($result[$group][self::CHILDREN]) as $key) {
                if (in_array($result[$group][self::CHILDREN][$key]['id'], self::MOLLIE_PAYMENT_METHODS, true)) {
                    $result = $this->moveMethodToRecommendedPaymentMethods($result, $group, $key);
                }
            }
        }

        uksort($result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN], static function ($a, $b) {
            if (in_array($a, self::MOLLIE_PAYMENT_METHODS, true)) {
                return -1;
            }
            if (in_array($b, self::MOLLIE_PAYMENT_METHODS, true)) {
                return 1;
            }
            return 0;
        });

        return $result;
    }

    public function moveMethodToOtherPaymentMethods(array $result, $key): array
    {
        if (isset($result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN][$key])) {
            $result[self::OTHER_PAYMENT_METHODS][self::CHILDREN] =
                array_merge(
                    [
                        $result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN][$key]
                    ],
                    $result[self::OTHER_PAYMENT_METHODS][self::CHILDREN]
                );

            unset($result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN][$key]);
        }
        return $result;
    }

    private function moveMethodToRecommendedPaymentMethods(array $result, string $sourceGroup, $key): array
    {
        $method = $result[$sourceGroup][self::CHILDREN][$key]['id'];
        $result[self::RECOMMENDED_SOLUTIONS][self::CHILDREN][$method] = $result[$sourceGroup][self::CHILDREN][$key];

        unset($result[$sourceGroup][self::CHILDREN][$key]);

        return $result;
    }
}
