<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Company\CompanyContactIdRequest;
use App\Dto\Admin\Company\CompanyContactSalvarRequest;
use App\Dto\Admin\Company\CompanyIdRequest;
use App\Dto\Admin\Company\CompanyIdsRequest;
use App\Dto\Admin\Company\CompanySalvarRequest;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\EmployeeRole;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Unit;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\CompanyService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $companyService;

    public function __construct(
        AdminAccessService $adminAccess,
        CompanyService $companyService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->companyService = $companyService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::COMPANY);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $usuario = $acceso['usuario'];
        $permiso = $acceso['permisos'];

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
            'permiso' => $permiso[0],
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
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'name', 'phone', 'address'],
                defaultOrderField: 'name'
            );

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
     * salvar Acción que inserta un menu en la BD.
     */
    public function salvar(Request $request)
    {
        $d = CompanySalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $company_id = (string) ($d->company_id ?? '');
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
            if ('' == $company_id) {
                $resultado = $this->companyService->SalvarCompany($name, $phone, $address, $contactName, $contactEmail, $email, $website, $contacts);
            } else {
                $resultado = $this->companyService->ActualizarCompany($company_id, $name, $phone, $address, $contactName, $contactEmail, $email, $website, $contacts);
            }

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
    public function eliminar(Request $request)
    {
        $dto = CompanyIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarCompanies(Request $request)
    {
        $dto = CompanyIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function cargarDatos(Request $request)
    {
        $dto = CompanyIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarContact(Request $request)
    {
        $dto = CompanyContactIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
     * salvarContact Acción que salvar un contact en la BD.
     */
    public function salvarContact(Request $request)
    {
        $d = CompanyContactSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }

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
     * listarContacts Acción que lista los contactos de la empresa en la BD.
     */
    public function listarContacts(Request $request)
    {
        $dto = CompanyIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
