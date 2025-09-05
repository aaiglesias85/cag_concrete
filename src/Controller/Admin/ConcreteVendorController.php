<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\ConcreteVendorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConcreteVendorController extends AbstractController
{

    private $concreteVendorService;

    public function __construct(ConcreteVendorService $concreteVendorService)
    {
        $this->concreteVendorService = $concreteVendorService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->concreteVendorService->BuscarPermiso($usuario->getUsuarioId(), 21);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/concrete-vendor/index.html.twig', array(
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
     * salvar Acción que inserta un conc vendor en la BD
     *
     */
    public function salvar(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $contactName = $request->get('contactName');
        $contactEmail = $request->get('contactEmail');

        // contacts
        $contacts = $request->get('contacts');
        $contacts = json_decode($contacts);

        try {

            if ($vendor_id == "") {
                $resultado = $this->concreteVendorService->SalvarVendor($name, $phone, $address, $contactName, $contactEmail, $contacts);
            } else {
                $resultado = $this->concreteVendorService->ActualizarVendor($vendor_id, $name, $phone, $address, $contactName, $contactEmail, $contacts);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor_id'] = $resultado['vendor_id'];
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
     * eliminar Acción que elimina un subcontractor en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        try {
            $resultado = $this->concreteVendorService->EliminarVendor($vendor_id);
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
     * eliminarVendors Acción que elimina los companies seleccionados en la BD
     *
     */
    public function eliminarVendors(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->concreteVendorService->EliminarVendors($ids);
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
     * cargarDatos Acción que carga los datos del subcontractor en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        try {
            $resultado = $this->concreteVendorService->CargarDatosVendor($vendor_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor'] = $resultado['vendor'];

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
     * listarContacts Acción que lista los contacts de un concrete vendor
     *
     */
    public function listarContacts(Request $request)
    {

        $vendor_id = $request->get('vendor_id');

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
     * eliminarContact Acción que elimina un contact en la BD
     *
     */
    public function eliminarContact(Request $request)
    {
        $contact_id = $request->get('contact_id');

        try {
            $resultado = $this->concreteVendorService->EliminarContact($contact_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
