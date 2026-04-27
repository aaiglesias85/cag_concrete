<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Log\LogIdRequest;
use App\Dto\Admin\Log\LogIdsRequest;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\LogService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $logService;

    public function __construct(
        AdminAccessService $adminAccess,
        LogService $logService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->logService = $logService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::LOG);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/log/index.html.twig', [
            'permiso' => $permiso[0],
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    public function listar(Request $request)
    {
        try {
            $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
            if ($g instanceof RedirectResponse) {
                return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
            }
            $usuario = $g;

            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'fecha', 'usuario', 'operacion', 'categoria', 'descripcion', 'ip'],
                defaultOrderField: 'fecha'
            );

            // filtros
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            $usuario_id = $usuario->isAdministrador() ? '' : $usuario->getUsuarioId();

            // total + data en una sola llamada a tu servicio
            $result = $this->logService->ListarLogs(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
                $usuario_id,
            );

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un log en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = LogIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $log_id = $dto->log_id;

        $resultado = $this->logService->EliminarLog($log_id);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = 'The operation was successful';

            return $this->json($resultadoJson);
        }
        $resultadoJson['success'] = $resultado['success'];
        $resultadoJson['error'] = $resultado['error'];

        return $this->json($resultadoJson);
    }

    /**
     * eliminarLogs Acción que elimina los loges seleccionados en la BD.
     */
    public function eliminarLogs(Request $request)
    {
        $idsDto = LogIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $idsDto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $idsDto->ids;

        $resultado = $this->logService->EliminarLogs($ids);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = 'The operation was successful';

            return $this->json($resultadoJson);
        }
        $resultadoJson['success'] = $resultado['success'];
        $resultadoJson['error'] = $resultado['error'];

        return $this->json($resultadoJson);
    }
}
