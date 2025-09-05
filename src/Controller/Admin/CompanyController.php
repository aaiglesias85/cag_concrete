<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\CompanyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CompanyController extends AbstractController
{

    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->companyService->BuscarPermiso($usuario->getUsuarioId(), 8);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/company/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los companies
     *
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
                'draw'            => $dt['draw'],
                'data'            => $result['data'],
                'recordsTotal'    => (int) $result['total'],
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
     * salvar Acción que inserta un menu en la BD
     *
     */
    public function salvar(Request $request)
    {
        $company_id = $request->get('company_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $contactName = $request->get('contactName');
        $contactEmail = $request->get('contactEmail');

        // contacts
        $contacts = $request->get('contacts');
        $contacts = json_decode($contacts);

        try {

            if ($company_id == "") {
                $resultado = $this->companyService->SalvarCompany($name, $phone, $address, $contactName, $contactEmail, $contacts);
            } else {
                $resultado = $this->companyService->ActualizarCompany($company_id, $name, $phone, $address, $contactName, $contactEmail, $contacts);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['company_id'] = $resultado['company_id'];

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un company en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $company_id = $request->get('company_id');

        try {
            $resultado = $this->companyService->EliminarCompany($company_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * eliminarCompanies Acción que elimina los companies seleccionados en la BD
     *
     */
    public function eliminarCompanies(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->companyService->EliminarCompanies($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * cargarDatos Acción que carga los datos del company en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $company_id = $request->get('company_id');

        try {
            $resultado = $this->companyService->CargarDatosCompany($company_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['company'] = $resultado['company'];

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

    /**
     * eliminarContact Acción que elimina un contact en la BD
     *
     */
    public function eliminarContact(Request $request)
    {
        $contact_id = $request->get('contact_id');

        try {
            $resultado = $this->companyService->EliminarContact($contact_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * salvarContact Acción que salvar un contact en la BD
     *
     */
    public function salvarContact(Request $request)
    {
        $company_id = $request->get('company_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $email = $request->get('email');
        $role = $request->get('role');
        $notes = $request->get('notes');

        try {
            $resultado = $this->companyService->SalvarContact($company_id, $name, $phone, $email, $role, $notes);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['contact_id'] = $resultado['contact_id'];

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * listarContacts Acción que lista los contactos de la empresa en la BD
     *
     */
    public function listarContacts(Request $request)
    {
        $company_id = $request->get('company_id');


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
