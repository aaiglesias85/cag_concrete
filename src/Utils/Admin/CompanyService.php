<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Project;
use App\Utils\Base;

class CompanyService extends Base
{

    /**
     * EliminarContact: Elimina un contact en la BD
     * @param int $contact_id Id
     * @author Marcel
     */
    public function EliminarContact($contact_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(CompanyContact::class)
            ->find($contact_id);
        /**@var CompanyContact $entity */
        if ($entity != null) {

            $contact_name = $entity->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Contact";
            $log_descripcion = "The company contact is deleted: $contact_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosCompany: Carga los datos de un company
     *
     * @param int $company_id Id
     *
     * @author Marcel
     */
    public function CargarDatosCompany($company_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Company::class)
            ->find($company_id);
        /** @var Company $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['address'] = $entity->getAddress();
            $arreglo_resultado['contactName'] = $entity->getContactName();
            $arreglo_resultado['contactEmail'] = $entity->getContactEmail();

            // contacts
            $contacts = $this->ListarContacts($company_id);
            $arreglo_resultado['contacts'] = $contacts;

            // projects
            $projects = $this->ListarProjects($company_id);
            $arreglo_resultado['projects'] = $projects;

            $resultado['success'] = true;
            $resultado['company'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarProjects
     * @param $company_id
     * @return array
     */
    public function ListarProjects($company_id)
    {
        $projects = [];

        $company_projects = $this->getDoctrine()->getRepository(Project::class)
            ->ListarProjectsDeCompany($company_id);

        foreach ($company_projects as $key => $value) {
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                "id" => $project_id,
                "projectNumber" => $value->getProjectNumber(),
                "name" => $value->getName(),
                "company" => $value->getCompany()->getName(),
                "county" => $value->getCounty(),
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
     * ListarContacts
     * @param $company_id
     * @return array
     */
    public function ListarContacts($company_id)
    {
        $contacts = [];

        $company_contacts = $this->getDoctrine()->getRepository(CompanyContact::class)
            ->ListarContacts($company_id);
        foreach ($company_contacts as $key => $contact) {
            $contacts[] = [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'role' => $contact->getRole(),
                'notes' => $contact->getNotes(),
                'posicion' => $key
            ];
        }

        return $contacts;
    }

    /**
     * EliminarCompany: Elimina un rol en la BD
     * @param int $company_id Id
     * @author Marcel
     */
    public function EliminarCompany($company_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Company::class)
            ->find($company_id);
        /**@var Company $entity */
        if ($entity != null) {

            // projects
            $projects = $this->getDoctrine()->getRepository(Project::class)
                ->ListarProjectsDeCompany($company_id);
            if (count($projects) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The company could not be deleted, because it is related to a project";
                return $resultado;
            }

            // contacts
            $contacts = $this->getDoctrine()->getRepository(CompanyContact::class)
                ->ListarContacts($company_id);
            foreach ($contacts as $contact) {
                $em->remove($contact);
            }

            $company_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Company";
            $log_descripcion = "The company is deleted: $company_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarCompanies: Elimina los companies seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarCompanies($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $company_id) {
                if ($company_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Company::class)
                        ->find($company_id);
                    /**@var Company $entity */
                    if ($entity != null) {

                        // projects
                        $projects = $this->getDoctrine()->getRepository(Project::class)
                            ->ListarProjectsDeCompany($company_id);
                        if (count($projects) == 0) {
                            // contacts
                            $contacts = $this->getDoctrine()->getRepository(CompanyContact::class)
                                ->ListarContacts($company_id);
                            foreach ($contacts as $contact) {
                                $em->remove($contact);
                            }

                            $company_descripcion = $entity->getName();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Company";
                            $log_descripcion = "The company is deleted: $company_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The companies could not be deleted, because they are associated with a project";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected companies because they are associated with a project";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarCompany: Actuializa los datos del rol en la BD
     * @param int $company_id Id
     * @author Marcel
     */
    public function ActualizarCompany($company_id, $name, $phone, $address, $contactName, $contactEmail, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Company::class)
            ->find($company_id);
        /** @var Company $entity */
        if ($entity != null) {
            //Verificar description
            $company = $this->getDoctrine()->getRepository(Company::class)
                ->findOneBy(['name' => $name]);
            if ($company != null && $entity->getCompanyId() != $company->getCompanyId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The company name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setPhone($phone);
            $entity->setAddress($address);
            $entity->setContactName($contactName);
            $entity->setContactEmail($contactEmail);

            $entity->setUpdatedAt(new \DateTime());

            // save contacts
            $this->SalvarContacts($entity, $contacts);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Company";
            $log_descripcion = "The company is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarCompany: Guarda los datos de company en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarCompany($name, $phone, $address, $contactName, $contactEmail, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar email
        $company = $this->getDoctrine()->getRepository(Company::class)
            ->findOneBy(['name' => $name]);
        if ($company != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The company name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Company();

        $entity->setName($name);
        $entity->setPhone($phone);
        $entity->setAddress($address);
        $entity->setContactName($contactName);
        $entity->setContactEmail($contactEmail);

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        // save contacts
        $this->SalvarContacts($entity, $contacts);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Company";
        $log_descripcion = "The company is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * SalvarContacts
     * @param $contacts
     * @param Company $entity
     * @return void
     */
    public function SalvarContacts($entity, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Senderos
        foreach ($contacts as $value) {

            $contact_entity = null;

            if (is_numeric($value->contact_id)) {
                $contact_entity = $this->getDoctrine()->getRepository(CompanyContact::class)
                    ->find($value->contact_id);
            }

            $is_new_contact = false;
            if ($contact_entity == null) {
                $contact_entity = new CompanyContact();
                $is_new_contact = true;
            }

            $contact_entity->setName($value->name);
            $contact_entity->setEmail($value->email);
            $contact_entity->setPhone($value->phone);
            $contact_entity->setRole($value->role);
            $contact_entity->setNotes($value->notes);

            if ($is_new_contact) {
                $contact_entity->setCompany($entity);

                $em->persist($contact_entity);
            }
        }
    }

    /**
     * ListarCompanies: Listar los companies
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarCompanies($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Company::class)
            ->ListarCompanies($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $company_id = $value->getCompanyId();

            $acciones = $this->ListarAcciones($company_id);

            $arreglo_resultado[$cont] = array(
                "id" => $company_id,
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
     * TotalCompanies: Total de companies
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalCompanies($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Company::class)
            ->TotalCompanies($sSearch);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 8);

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