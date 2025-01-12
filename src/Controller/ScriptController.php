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
}
