<?php

namespace Ebizmarts\BrippoPayments\Ui\Component\Listing\Column;

use Ebizmarts\BrippoPayments\Model\OrderGridInfo;

class OrderGridColumns extends OrderGridInfo
{

    /**
     * @param array $additional
     * @param string $index
     * @return string
     */
    public function getIconClass(array $additional, $index)
    {
        $status = $this->getStatus($additional, $index);
        return $this->getIconClassByStatus($status);
    }

    /**
     * @param $status
     * @return string
     */
    public function getIconClassByStatus($status): string
    {
        $status = strtoupper($status);

        switch ($status) {
            case 'ELEVATED':
                $iconClass = '<div class="brippo-fraud-risk elevated" title="Fraud risk is elevated">&#9888;</div>';
                break;
            case 'HIGHEST':
                $iconClass = '<div class="brippo-fraud-risk highest" title="Fraud risk is highest">&#9888;</div>';
                break;
            case 'FAIL':
                $iconClass = '<div class="brippo-fraud-check fail" title="Check failed"></div>';
                break;
            case 'NORMAL':
                $iconClass = '<div class="brippo-fraud-risk normal" title="Fraud risk is normal">&#10003;</div>';
                break;
            case 'PASS':
                $iconClass = '<div class="brippo-fraud-check pass" title="Check passed">&#10003;</div>';
                break;
            case 'UNCHECKED':
                $iconClass = '<div class="brippo-fraud-check unchecked" title="Unchecked">&#9888;</div>';
                break;
            case 'PASSED':
                $iconClass = '<div class="brippo-fraud-check pass" title="3D Secure passed">&#10003;</div>';
                break;
            case 'REJECTED':
                $iconClass = '<div class="brippo-fraud-check fail" title="3D Secure rejected"></div>';
                break;
            case 'FAILED':
                $iconClass = '<div class="brippo-fraud-check fail" title="3D Secure failed"></div>';
                break;
            case 'ATTEMPTED':
                $iconClass = '<div class="brippo-fraud-check unchecked" title="3D Secure attempted">&#9888;</div>';
                break;
            case 'NOT_PRESENT':
                $iconClass = '<div class="brippo-fraud-risk not_assessed" title="3D Secure not present">&#9888;</div>';
                break;
            case self::FRAUD_STATE_RELOAD_REQUIRED:
                $iconClass = '<div class="brippo-fraud-check please-reload"
                           title="Max checks per page reached, please reload to try again">&#8634;</div>';
                break;
            case self::FRAUD_STATE_NOT_AVAILABLE:
                $iconClass = '<div class="brippo-fraud-check not-available"
                           title="Fraud information not available">-</div>';
                break;
            default:
                $iconClass = '<div class="brippo-fraud-risk not_assessed" title="Not assessed">&#9888;</div>';
                break;
        }

        return $iconClass;
    }

    /**
     * @param $additional
     * @param $index
     * @return string
     */
    public function getStatus($additional, $index): string
    {
        if ($index === self::FRAUD_STATE_RELOAD_REQUIRED) {
            return self::FRAUD_STATE_RELOAD_REQUIRED;
        } elseif ($index === self::FRAUD_STATE_NOT_AVAILABLE) {
            return self::FRAUD_STATE_NOT_AVAILABLE;
        }
        return $additional[$index] ?? "NOTPROVIDED";
    }
}
