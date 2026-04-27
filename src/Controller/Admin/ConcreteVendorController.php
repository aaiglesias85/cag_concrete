<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorContactIdRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorIdRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorIdsRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorSalvarRequest;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ConcreteVendorService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConcreteVendorController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $concreteVendorService;

    public function __construct(
        AdminAccessService $adminAccess,
        ConcreteVendorService $concreteVendorService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->concreteVendorService = $concreteVendorService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::CONCRETE_VENDOR);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/concrete-vendor/index.html.twig', [
            'permiso' => $permiso[0],
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'name', 'email', 'phone', 'address'],
                defaultOrderField: 'name'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->concreteVendorService->ListarVendors(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
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
     * salvar Acción que inserta un conc vendor en la BD.
     */
    public function salvar(Request $request)
    {
        $d = ConcreteVendorSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $vendor_id = (string) ($d->vendor_id ?? '');
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $contacts = null !== $d->contacts && '' !== $d->contacts ? json_decode($d->contacts) : null;

        try {
            if ('' == $vendor_id) {
                $resultado = $this->concreteVendorService->SalvarVendor($name, $phone, $address, $contactName, $contactEmail, $contacts);
            } else {
                $resultado = $this->concreteVendorService->ActualizarVendor($vendor_id, $name, $phone, $address, $contactName, $contactEmail, $contacts);
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor_id'] = $resultado['vendor_id'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un subcontractor en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = ConcreteVendorIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $vendor_id = $dto->vendor_id;

        try {
            $resultado = $this->concreteVendorService->EliminarVendor($vendor_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarVendors Acción que elimina los companies seleccionados en la BD.
     */
    public function eliminarVendors(Request $request)
    {
        $idsDto = ConcreteVendorIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $idsDto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->concreteVendorService->EliminarVendors($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * cargarDatos Acción que carga los datos del subcontractor en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = ConcreteVendorIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $vendor_id = $dto->vendor_id;

        try {
            $resultado = $this->concreteVendorService->CargarDatosVendor($vendor_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor'] = $resultado['vendor'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarContacts Acción que lista los contacts de un concrete vendor.
     */
    public function listarContacts(Request $request)
    {
        $dto = ConcreteVendorIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $vendor_id = $dto->vendor_id;

        try {
            $contacts = $this->concreteVendorService->ListarContactsDeConcreteVendor($vendor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['contacts'] = $contacts;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarContact Acción que elimina un contact en la BD.
     */
    public function eliminarContact(Request $request)
    {
        $dto = ConcreteVendorContactIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $contact_id = $dto->contact_id;

        try {
            $resultado = $this->concreteVendorService->EliminarContact($contact_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
