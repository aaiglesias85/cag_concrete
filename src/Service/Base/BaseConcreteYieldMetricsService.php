<?php

namespace App\Service\Base;

use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use Doctrine\Persistence\ManagerRegistry;

class BaseConcreteYieldMetricsService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly BaseYieldExpressionService $yieldExpression,
    ) {
    }

    public function CalcularTotalConcreteYiel($data_tracking_id): float
    {
        $total_conc_yiel = 0;

        /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->doctrine->getRepository(DataTrackingItem::class);
        $data_tracking_items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
        foreach ($data_tracking_items as $data_tracking_item) {
            $quantity_yield = $this->CalcularTotalConcreteYielItem($data_tracking_item);
            $total_conc_yiel += $quantity_yield;
        }

        return $total_conc_yiel;
    }

    /**
     * @return float
     */
    public function CalcularTotalConcreteYielItem($data_tracking_item)
    {
        $quantity_yield = 0;

        if ('' != $data_tracking_item->getProjectItem()->getYieldCalculation() && 'none' != $data_tracking_item->getProjectItem()->getYieldCalculation()) {
            if ('equation' == $data_tracking_item->getProjectItem()->getYieldCalculation() && null != $data_tracking_item->getProjectItem()->getEquation()) {
                $quantity = $data_tracking_item->getQuantity();
                $quantity_yield = $this->yieldExpression->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
            } else {
                $quantity_yield = $data_tracking_item->getQuantity();
            }
        }

        return $quantity_yield;
    }

    public function CalcularLostConcrete(DataTracking $value): float
    {
        $total_conc_item = 0;

        $data_tracking_id = $value->getId();

        /** @var \App\Repository\DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
        $dataTrackingConcVendorRepo = $this->doctrine->getRepository(DataTrackingConcVendor::class);
        $total_conc_used = $dataTrackingConcVendorRepo->TotalConcUsed((string) $data_tracking_id);

        /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->doctrine->getRepository(DataTrackingItem::class);
        $data_tracking_items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
        foreach ($data_tracking_items as $data_tracking_item) {
            $quantity = $data_tracking_item->getQuantity();
            $quantity_yield = $quantity;
            if ('equation' == $data_tracking_item->getProjectItem()->getYieldCalculation() && null != $data_tracking_item->getProjectItem()->getEquation()) {
                $quantity_yield = $this->yieldExpression->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
            }

            $total_conc_item += $quantity_yield;
        }

        return round($total_conc_used - $total_conc_item, 2);
    }
}
