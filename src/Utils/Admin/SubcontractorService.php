<?php

namespace App\Utils\Admin;

use App\Entity\Subcontractor;
use App\Entity\SubcontractorEmployee;
use App\Entity\SubcontractorNotes;
use App\Utils\Base;

class SubcontractorService extends Base
{

    /**
     * CargarDatosEmployee: Carga los datos de un employee
     *
     * @param int $employee_id Id
     *
     * @author Marcel
     */
    public function CargarDatosEmployee($employee_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
            ->find($employee_id);
        /** @var SubcontractorEmployee $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['hourly_rate'] = $entity->getHourlyRate();
            $arreglo_resultado['position'] = $entity->getPosition();

            $resultado['success'] = true;
            $resultado['employee'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * SalvarEmployee
     * @return array
     */
    public function SalvarEmployee($employee_id, $subcontractor_id, $name, $hourly_rate, $position)
    {

        $em = $this->getDoctrine()->getManager();

        $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /** @var Subcontractor $subcontractor_entity */
        if ($subcontractor_entity != null) {

            $entity = null;
            $is_new = false;

            if (is_numeric($employee_id)) {
                $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
                    ->find($employee_id);
            }

            if ($entity == null) {
                $entity = new SubcontractorEmployee();
                $is_new = true;
            }

            $entity->setName($name);
            $entity->setHourlyRate($hourly_rate);
            $entity->setPosition($position);

            $entity->setSubcontractor($subcontractor_entity);

            $log_operacion = "Add";
            $log_descripcion = "The employee: $name is add to the subcontractor: " . $subcontractor_entity->getName();

            if ($is_new) {
                $em->persist($entity);
            } else {
                $log_operacion = "Update";
                $log_descripcion = "The employee: $name is modified to the subcontractor: " . $subcontractor_entity->getName();
            }

            $em->flush();

            //Salvar log
            $log_categoria = "Subcontractor Employee";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The subcontractor not exist.";
        }

        return $resultado;

    }

    /**
     * EliminarEmployee: Elimina un employee en la BD
     * @param int $employee_id Id
     * @author Marcel
     */
    public function EliminarEmployee($employee_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
            ->find($employee_id);
        /**@var SubcontractorEmployee $entity */
        if ($entity != null) {
            $name = $entity->getName();
            $subcontractor_name = $entity->getSubcontractor()->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Subcontractor Employee";
            $log_descripcion = "The employee: $name is delete from subcontractor: $subcontractor_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * ListarEmployees: Listar los employees
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
            ->ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id);

        foreach ($lista as $value) {
            $employee_id = $value->getEmployeeId();

            $acciones = $this->ListarAccionesEmployees($employee_id);

            $arreglo_resultado[$cont] = array(
                "id" => $employee_id,
                "name" => $value->getName(),
                "hourlyRate" => $value->getHourlyRate(),
                "position" => $value->getPosition(),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalEmployees: Total de employees
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalEmployees($sSearch, $subcontractor_id)
    {
        $total = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
            ->TotalEmployees($sSearch, $subcontractor_id);

        return $total;
    }

    /**
     * ListarAccionesEmployees: Lista las acciones
     *
     * @author Marcel
     */
    public function ListarAccionesEmployees($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 18);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
        }

        return $acciones;
    }

    /**
     * EliminarNotes: Elimina un notes en la BD
     * @param int $notes_id Id
     * @author Marcel
     */
    public function EliminarNotes($notes_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
            ->find($notes_id);
        /**@var SubcontractorNotes $entity */
        if ($entity != null) {
            $notes = $entity->getNotes();
            $subcontractor_name = $entity->getSubcontractor()->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Subcontractor Notes";
            $log_descripcion = "The notes: $notes is delete from subcontractor: $subcontractor_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarNotesDate: Elimina un notes en un rango de fechas en la BD
     * @param int $subcontractor_id Id
     * @author Marcel
     */
    public function EliminarNotesDate($subcontractor_id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /** @var Subcontractor $subcontractor_entity */
        if ($subcontractor_entity != null) {

            $subcontractor_name = $subcontractor_entity->getName();


            $notes = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
                ->ListarNotesDeSubcontractor($subcontractor_id, $from, $to);
            foreach ($notes as $entity) {
                $em->remove($entity);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Subcontractor Notes";
            $log_descripcion = "The notes $from and $to is delete from subcontractor: $subcontractor_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosNotes: Carga los datos de un notes
     *
     * @param int $notes_id Id
     *
     * @author Marcel
     */
    public function CargarDatosNotes($notes_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
            ->find($notes_id);
        /** @var SubcontractorNotes $entity */
        if ($entity != null) {

            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');

            $resultado['success'] = true;
            $resultado['notes'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * SalvarNotes
     * @param $notes_id
     * @param $subcontractor_id
     * @param $notes
     * @param $date
     * @return array
     */
    public function SalvarNotes($notes_id, $subcontractor_id, $notes, $date)
    {

        $em = $this->getDoctrine()->getManager();

        $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /** @var Subcontractor $subcontractor_entity */
        if ($subcontractor_entity != null) {

            $entity = null;
            $is_new = false;

            if (is_numeric($notes_id)) {
                $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
                    ->find($notes_id);
            }

            if ($entity == null) {
                $entity = new SubcontractorNotes();
                $is_new = true;
            }

            $entity->setNotes($notes);

            if ($date != '') {
                $date = \DateTime::createFromFormat('m/d/Y', $date);
                $entity->setDate($date);
            }

            $entity->setSubcontractor($subcontractor_entity);

            $log_operacion = "Add";
            $log_descripcion = "The notes: $notes is add to the subcontractor: " . $subcontractor_entity->getName();

            if ($is_new) {
                $em->persist($entity);
            } else {
                $log_operacion = "Update";
                $log_descripcion = "The notes: $notes is modified to the subcontractor: " . $subcontractor_entity->getName();
            }

            $em->flush();

            //Salvar log
            $log_categoria = "Subcontractor Notes";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The subcontractor not exist.";
        }

        return $resultado;

    }

    /**
     * ListarNotes: Listar los notes
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
            ->ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {
            $notes_id = $value->getId();

            $acciones = $this->ListarAccionesNotes($notes_id);

            $notes = $value->getNotes();
            $notes = mb_convert_encoding($notes, 'UTF-8', 'UTF-8');

            $arreglo_resultado[$cont] = array(
                "id" => $notes_id,
                "notes" => $notes,
                "date" => $value->getDate()->format('m/d/Y'),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalNotes: Total de notes
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalNotes($sSearch, $subcontractor_id, $fecha_inicial, $fecha_fin)
    {
        $total = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
            ->TotalNotes($sSearch, $subcontractor_id, $fecha_inicial, $fecha_fin);

        return $total;
    }

    /**
     * ListarAccionesNotes: Lista las acciones
     *
     * @author Marcel
     */
    public function ListarAccionesNotes($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 18);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
        }

        return $acciones;
    }

    /**
     * CargarDatosSubcontractor: Carga los datos de un subcontractor
     *
     * @param int $subcontractor_id Id
     *
     * @author Marcel
     */
    public function CargarDatosSubcontractor($subcontractor_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /** @var Subcontractor $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['address'] = $entity->getAddress();
            $arreglo_resultado['contactName'] = $entity->getContactName();
            $arreglo_resultado['contactEmail'] = $entity->getContactEmail();

            $resultado['success'] = true;
            $resultado['subcontractor'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarSubcontractor: Elimina un rol en la BD
     * @param int $subcontractor_id Id
     * @author Marcel
     */
    public function EliminarSubcontractor($subcontractor_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /**@var Subcontractor $entity */
        if ($entity != null) {

            // eliminar informacion
            $this->EliminarInformacionDeSubcontractor($subcontractor_id);

            $subcontractor_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Subcontractor";
            $log_descripcion = "The subcontractor is deleted: $subcontractor_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarSubcontractors: Elimina los subcontractors seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarSubcontractors($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $subcontractor_id) {
                if ($subcontractor_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
                        ->find($subcontractor_id);
                    /**@var Subcontractor $entity */
                    if ($entity != null) {

                        // eliminar informacion
                        $this->EliminarInformacionDeSubcontractor($subcontractor_id);

                        $subcontractor_descripcion = $entity->getName();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Subcontractor";
                        $log_descripcion = "The subcontractor is deleted: $subcontractor_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The subcontractors could not be deleted, because they are associated with a subcontractor";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected subcontractors because they are associated with a subcontractor";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeSubcontractor
     * @param $subcontractor_id
     * @return void
     */
    private function EliminarInformacionDeSubcontractor($subcontractor_id)
    {
        $em = $this->getDoctrine()->getManager();

        // employees
        $employees = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
            ->ListarEmployeesDeSubcontractor($subcontractor_id);
        foreach ($employees as $employee) {
            $em->remove($employee);
        }

        // notes
        $notes = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
            ->ListarNotesDeSubcontractor($subcontractor_id);
        foreach ($notes as $note) {
            $em->remove($note);
        }
    }

    /**
     * ActualizarSubcontractor: Actuializa los datos del rol en la BD
     * @param int $subcontractor_id Id
     * @author Marcel
     */
    public function ActualizarSubcontractor($subcontractor_id, $name, $phone, $address, $contactName, $contactEmail)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->find($subcontractor_id);
        /** @var Subcontractor $entity */
        if ($entity != null) {
            //Verificar description
            $subcontractor = $this->getDoctrine()->getRepository(Subcontractor::class)
                ->findOneBy(['name' => $name]);
            if ($subcontractor != null && $entity->getSubcontractorId() != $subcontractor->getSubcontractorId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The subcontractor name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setPhone($phone);
            $entity->setAddress($address);
            $entity->setContactName($contactName);
            $entity->setContactEmail($contactEmail);

            $entity->setUpdatedAt(new \DateTime());

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Subcontractor";
            $log_descripcion = "The subcontractor is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarSubcontractor: Guarda los datos de subcontractor en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarSubcontractor($name, $phone, $address, $contactName, $contactEmail)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar email
        $subcontractor = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->findOneBy(['name' => $name]);
        if ($subcontractor != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The subcontractor name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Subcontractor();

        $entity->setName($name);
        $entity->setPhone($phone);
        $entity->setAddress($address);
        $entity->setContactName($contactName);
        $entity->setContactEmail($contactEmail);

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Subcontractor";
        $log_descripcion = "The subcontractor is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarSubcontractors: Listar los subcontractors
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->ListarSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $subcontractor_id = $value->getSubcontractorId();

            $acciones = $this->ListarAcciones($subcontractor_id);

            $arreglo_resultado[$cont] = array(
                "id" => $subcontractor_id,
                "name" => $value->getName(),
                "phone" => $value->getPhone(),
                "address" => $value->getAddress(),
                "contactName" => $value->getContactName(),
                "contactEmail" => $value->getContactEmail(),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalSubcontractors: Total de subcontractors
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalSubcontractors($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->TotalSubcontractors($sSearch);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 18);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}