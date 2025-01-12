<?php

namespace App\Utils;

use App\Entity\DataTracking;
use App\Entity\DataTrackingItem;

class ScriptService extends Base
{



    /**
     * DefinirPendingDataTracking
     */
    public function DefinirPendingDataTracking()
    {
        $em = $this->getDoctrine()->getManager();

        // listar datatracking
        $data_trackings = $this->getDoctrine()->getRepository(DataTracking::class)
            ->findAll();
        foreach ($data_trackings as $data_tracking) {


            $pending = false;
            $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)->ListarItems($data_tracking->getId());
            foreach ($items as $value) {
                if($value->getQuantity() == 0){
                    $pending = true;
                }
            }

            // pending
            $data_tracking->setPending($pending);
        }

        $em->flush();


    }
}