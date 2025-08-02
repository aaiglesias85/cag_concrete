<?php

namespace App\Controller;

use App\Utils\ScriptService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ScriptController extends AbstractController
{

    private $scriptService;

    public function __construct(ScriptService $scriptService)
    {
        $this->scriptService = $scriptService;
    }

    public function definirpendingdatatracking()
    {
        $this->scriptService->DefinirPendingDataTracking();

        return new Response('OK', 200);
    }

    public function definirnotificacionesduedate()
    {
        $this->scriptService->DefinirNotificacionesDueDate();

        return new Response('OK', 200);
    }

    public function definiryieldcalculationitem()
    {
        $this->scriptService->DefinirYieldCalculationItem();

        return new Response('OK', 200);
    }

    public function definirsubcontractordatatrackingprojectitem()
    {
        $this->scriptService->DefinirSubcontractorDatatrackingProjectItem();

        return new Response('OK', 200);
    }

    public function definirconcretevendordatatracking()
    {
        $this->scriptService->DefinirConcreteVendorDataTracking();

        return new Response('OK', 200);
    }

    public function definircountyprojectestimate()
    {
        $this->scriptService->DefinirCountyProjectEstimate();

        return new Response('OK', 200);
    }

    public function cronreminders()
    {
        $this->scriptService->CronReminders();

        return new Response('OK', 200);
    }
}
