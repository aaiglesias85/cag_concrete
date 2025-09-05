<?php

namespace App\Utils\Admin;

use App\Entity\ConcreteVendor;
use App\Entity\ConcreteVendorContact;
use App\Entity\DataTrackingConcVendor;
use App\Entity\Project;
use App\Entity\Schedule;
use App\Entity\ScheduleConcreteVendorContact;
use App\Utils\Base;

class ConcreteVendorService extends Base
{

    /**
     * EliminarContact: Elimina un contact en la BD
     * @param int $contact_id Id
     * @author Marcel
     */
    public function EliminarContact($contact_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ConcreteVendorContact::class)
            ->find($contact_id);
        /**@var ConcreteVendorContact $entity */
        if ($entity != null) {

            $contact_name = $entity->getName();

            // schedules
            $schedules = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class)
                ->ListarSchedulesDeContact($contact_id);
            foreach ($schedules as $schedule) {
                $em->remove($schedule);
            }

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Concrete Vendor Contact";
            $log_descripcion = "The concrete vendor contact is deleted: $contact_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * ListarOrdenados
     * @return array
     */
    public function ListarOrdenados()
    {
        $vendors = [];

        $lista = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->ListarOrdenados();

        foreach ($lista as $value) {
            $vendors[] = [
                'vendor_id' => $value->getVendorId(),
                'name' => $value->getName(),
            ];

        }

        return $vendors;
    }

    /**
     * CargarDatosVendor: Carga los datos de un vendor
     *
     * @param int $vendor_id Id
     *
     * @author Marcel
     */
    public function CargarDatosVendor($vendor_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->find($vendor_id);
        /** @var ConcreteVendor $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['address'] = $entity->getAddress();

            $arreglo_resultado['contactName'] = $entity->getContactName();
            $arreglo_resultado['contactEmail'] = $entity->getContactEmail();

            // contacts
            $contacts = $this->ListarContactsDeConcreteVendor($vendor_id);
            $arreglo_resultado['contacts'] = $contacts;

            // projects
            $projects = $this->ListarProjects($vendor_id);
            $arreglo_resultado['projects'] = $projects;

            $resultado['success'] = true;
            $resultado['vendor'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarProjects
     * @param $vendor_id
     * @return array
     */
    public function ListarProjects($vendor_id)
    {
        $projects = [];

        $vendor_projects = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->ListarProjectsDeConcVendor($vendor_id);

        foreach ($vendor_projects as $key => $vendor_project) {
            $value = $vendor_project->getDataTracking()->getProject();
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                "id" => $project_id,
                "projectNumber" => $value->getProjectNumber(),
                "name" => $value->getName(),
                "description" => $value->getDescription(),
                "company" => $value->getCompany()->getName(),
                "county" => $value->getCountyObj() ? $value->getCountyObj()->getDescription() : "",
                "status" => $value->getStatus(),
                "startDate" => $value->getStartDate() != '' ? $value->getStartDate()->format('m/d/Y') : '',
                "endDate" => $value->getEndDate() != '' ? $value->getEndDate()->format('m/d/Y') : '',
                "dueDate" => $value->getDueDate() != '' ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                'posicion' => $key
            ];
        }

        return $projects;
    }

    /**
     * EliminarVendor: Elimina un concrete vendor en la BD
     * @param int $vendor_id Id
     * @author Marcel
     */
    public function EliminarVendor($vendor_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->find($vendor_id);
        /**@var ConcreteVendor $entity */
        if ($entity != null) {

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($vendor_id);

            $vendor_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Concrete Vendor";
            $log_descripcion = "The concrete vendor is deleted: $vendor_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    public function EliminarInformacionRelacionada($vendor_id)
    {
        $em = $this->getDoctrine()->getManager();

        // contacts
        $contacts = $this->getDoctrine()->getRepository(ConcreteVendorContact::class)
            ->ListarContacts($vendor_id);
        foreach ($contacts as $contact) {

            // schedules
            $schedules_contact = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class)
                ->ListarSchedulesDeContact($contact->getContactId());
            foreach ($schedules_contact as $schedule_contact) {
                $em->remove($schedule_contact);
            }

            $em->remove($contact);
        }

        // eliminar datatracking vendor
        $data_trackings = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->ListarDataTrackingsDeConcVendor($vendor_id);
        foreach ($data_trackings as $data_tracking) {
            $em->remove($data_tracking);
        }

        // schedules
        $schedules = $this->getDoctrine()->getRepository(Schedule::class)
            ->ListarSchedulesDeConcreteVendor($vendor_id);
        foreach ($schedules as $schedule) {
            $schedule->setConcreteVendor(NULL);
        }

    }

    /**
     * EliminarVendors: Elimina los vendors seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarVendors($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $vendor_id) {
                if ($vendor_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(ConcreteVendor::class)
                        ->find($vendor_id);
                    /**@var ConcreteVendor $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($vendor_id);

                        $vendor_descripcion = $entity->getName();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Concrete Vendor";
                        $log_descripcion = "The concrete vendor is deleted: $vendor_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The vendors could not be deleted, because they are associated with a vendor";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected vendors because they are associated with a vendor";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarVendor: Actuializa los datos del rol en la BD
     * @param int $vendor_id Id
     * @author Marcel
     */
    public function ActualizarVendor($vendor_id, $name, $phone, $address, $contactName, $contactEmail, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->find($vendor_id);
        /** @var ConcreteVendor $entity */
        if ($entity != null) {
            //Verificar description
            $vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
                ->findOneBy(['name' => $name]);
            if ($vendor != null && $entity->getVendorId() != $vendor->getVendorId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The concrete vendor name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setPhone($phone);
            $entity->setAddress($address);
            $entity->setContactName($contactName);
            $entity->setContactEmail($contactEmail);

            // save contacts
            $this->SalvarContacts($entity, $contacts);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Concrete Vendor";
            $log_descripcion = "The concrete vendor is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['vendor_id'] = $entity->getVendorId();

            return $resultado;
        }
    }

    /**
     * SalvarVendor: Guarda los datos de vendor en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarVendor($name, $phone, $address, $contactName, $contactEmail, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->findOneBy(['name' => $name]);
        if ($vendor != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The concrete vendor name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new ConcreteVendor();

        $entity->setName($name);
        $entity->setPhone($phone);
        $entity->setAddress($address);
        $entity->setContactName($contactName);
        $entity->setContactEmail($contactEmail);

        $em->persist($entity);

        // save contacts
        $this->SalvarContacts($entity, $contacts);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Concrete Vendor";
        $log_descripcion = "The concrete vendor is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['vendor_id'] = $entity->getVendorId();

        return $resultado;
    }

    /**
     * SalvarContacts
     * @param $contacts
     * @param ConcreteVendor $entity
     * @return void
     */
    public function SalvarContacts($entity, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Senderos
        foreach ($contacts as $value) {

            $contact_entity = null;

            if (is_numeric($value->contact_id)) {
                $contact_entity = $this->getDoctrine()->getRepository(ConcreteVendorContact::class)
                    ->find($value->contact_id);
            }

            $is_new_contact = false;
            if ($contact_entity == null) {
                $contact_entity = new ConcreteVendorContact();
                $is_new_contact = true;
            }

            $contact_entity->setName($value->name);
            $contact_entity->setEmail($value->email);
            $contact_entity->setPhone($value->phone);
            $contact_entity->setRole($value->role);
            $contact_entity->setNotes($value->notes);

            if ($is_new_contact) {
                $contact_entity->setConcreteVendor($entity);

                $em->persist($contact_entity);
            }
        }
    }

    /**
     * ListarVendors: Listar los vendors
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarVendors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $resultado = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->ListarVendorsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $vendor_id = $value->getVendorId();

            $data[] = array(
                "id" => $vendor_id,
                "name" => $value->getName(),
                "phone" => $value->getPhone() ?? '',
                "address" => $value->getAddress(),
                "contactName" => $value->getContactName(),
                "email" => $value->getContactEmail(),
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}