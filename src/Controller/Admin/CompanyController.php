<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Company\CompanyActualizarRequest;
use App\Dto\Admin\Company\CompanyContactActualizarRequest;
use App\Dto\Admin\Company\CompanyContactIdRequest;
use App\Dto\Admin\Company\CompanyContactSalvarRequest;
use App\Dto\Admin\Company\CompanyIdRequest;
use App\Dto\Admin\Company\CompanyIdsRequest;
use App\Dto\Admin\Company\CompanyListarRequest;
use App\Dto\Admin\Company\CompanySalvarRequest;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\EmployeeRole;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Unit;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\CompanyService;
use Symfony\Component\HttpFoundation\JsonResponse;

class CompanyController extends AbstractAdminController
{
    private $companyService;

    public function __construct(
        AdminAccessService $adminAccess,
        CompanyService $companyService)
    {
        parent::__construct($adminAccess);
        $this->companyService = $companyService;
    }

    #[RequireAdminPermission(FunctionId::COMPANY)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::COMPANY);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso COMPANY esperado tras #[RequireAdminPermission].');

        $doctrine = $this->companyService->getDoctrine();
        $countys = $doctrine->getRepository(County::class)->ListarOrdenados('', '', '');
        $inspectors = $doctrine->getRepository(Inspector::class)->ListarOrdenados();
        $items = $doctrine->getRepository(Item::class)->ListarOrdenados();
        $units = $doctrine->getRepository(Unit::class)->ListarOrdenados();
        $equations = $doctrine->getRepository(Equation::class)->ListarOrdenados();
        $concrete_vendors = $doctrine->getRepository(ConcreteVendor::class)->ListarOrdenados();
        $concrete_classes = $doctrine->getRepository(ConcreteClass::class)->ListarOrdenados();
        $employee_roles = $doctrine->getRepository(EmployeeRole::class)->ListarOrdenados();
        $yields_calculation = $this->companyService->ListarYieldsCalculation();
        $usuario_retainage = $usuario->getRetainage() ?? false;
        $usuario_bond = $usuario->getBond() ?? false;

        return $this->render('admin/company/index.html.twig', [
            'permiso' => $permiso,
            'countys' => $countys,
            'inspectors' => $inspectors,
            'items' => $items,
            'units' => $units,
            'equations' => $equations,
            'concrete_vendors' => $concrete_vendors,
            'concrete_classes' => $concrete_classes,
            'employee_roles' => $employee_roles,
            'yields_calculation' => $yields_calculation,
            'usuario_retainage' => $usuario_retainage,
            'usuario_bond' => $usuario_bond,
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::View, jsonOnDenied: true)]
    public function listar(CompanyListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            // total + data en una sola llamada a tu servicio
            $result = $this->companyService->ListarCompanies(
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
     * salvar Acción que inserta un company en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(CompanySalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $email = (string) ($d->email ?? '');
        $website = (string) ($d->website ?? '');
        $contactsRaw = $d->contacts;
        $contacts = [];
        if (null !== $contactsRaw && '' !== (string) $contactsRaw) {
            $decoded = json_decode((string) $contactsRaw, false);
            $contacts = \is_array($decoded) ? $decoded : [];
        }

        try {
            $resultado = $this->companyService->SalvarCompany($name, $phone, $address, $contactName, $contactEmail, $email, $website, $contacts);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['company_id'] = $resultado['company_id'];

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
     * actualizar Acción que actualiza un company en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(CompanyActualizarRequest $d): JsonResponse
    {
        $company_id = (string) $d->company_id;
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $email = (string) ($d->email ?? '');
        $website = (string) ($d->website ?? '');
        $contactsRaw = $d->contacts;
        $contacts = [];
        if (null !== $contactsRaw && '' !== (string) $contactsRaw) {
            $decoded = json_decode((string) $contactsRaw, false);
            $contacts = \is_array($decoded) ? $decoded : [];
        }

        try {
            $resultado = $this->companyService->ActualizarCompany($company_id, $name, $phone, $address, $contactName, $contactEmail, $email, $website, $contacts);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['company_id'] = $resultado['company_id'];

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
     * eliminar Acción que elimina un company en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(CompanyIdRequest $dto): JsonResponse
    {
        $company_id = $dto->company_id;

        try {
            $resultado = $this->companyService->EliminarCompany($company_id);
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
     * eliminarCompanies Acción que elimina los companies seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarCompanies(CompanyIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->companyService->EliminarCompanies($ids);
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
     * cargarDatos Acción que carga los datos del company en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(CompanyIdRequest $dto): JsonResponse
    {
        $company_id = $dto->company_id;

        try {
            $resultado = $this->companyService->CargarDatosCompany($company_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['company'] = $resultado['company'];

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
     * eliminarContact Acción que elimina un contact en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarContact(CompanyContactIdRequest $dto): JsonResponse
    {
        $contact_id = $dto->contact_id;

        try {
            $resultado = $this->companyService->EliminarContact($contact_id);
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
     * salvarContact: alta de un contacto (modal rápido).
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarContact(CompanyContactSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->companyService->SalvarContact(
                $d->company_id,
                (string) $d->name,
                (string) ($d->phone ?? ''),
                (string) ($d->email ?? ''),
                (string) ($d->role ?? ''),
                (string) ($d->notes ?? '')
            );
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['contact_id'] = $resultado['contact_id'];

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
     * actualizarContact: edición de un contacto ya persistido.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarContact(CompanyContactActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->companyService->ActualizarContact(
                $d->contact_id,
                $d->company_id,
                (string) $d->name,
                (string) ($d->phone ?? ''),
                (string) ($d->email ?? ''),
                (string) ($d->role ?? ''),
                (string) ($d->notes ?? '')
            );
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['contact_id'] = $resultado['contact_id'];

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
     * listarContacts Acción que lista los contactos de la empresa en la BD.
     */
    #[RequireAdminPermission(FunctionId::COMPANY, AdminPermission::View, jsonOnDenied: true)]
    public function listarContacts(CompanyIdRequest $dto): JsonResponse
    {
        $company_id = $dto->company_id;

        try {
            $lista = $this->companyService->ListarContactsDeCompany($company_id);

            $resultadoJson['success'] = true;
            $resultadoJson['contacts'] = $lista;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
