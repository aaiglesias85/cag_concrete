<?php

namespace App\Service\Admin;

use App\Dto\Admin\Company\CompanyActualizarRequest;
use App\Dto\Admin\Company\CompanyContactActualizarRequest;
use App\Dto\Admin\Company\CompanyContactIdRequest;
use App\Dto\Admin\Company\CompanyContactSalvarRequest;
use App\Dto\Admin\Company\CompanyIdRequest;
use App\Dto\Admin\Company\CompanyIdsRequest;
use App\Dto\Admin\Company\CompanyListarRequest;
use App\Dto\Admin\Company\CompanySalvarRequest;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\Estimate;
use App\Entity\EstimateCompany;
use App\Entity\Project;
use App\Repository\CompanyContactRepository;
use App\Repository\CompanyRepository;
use App\Repository\EstimateCompanyRepository;
use App\Repository\ProjectRepository;
use App\Service\Base\Base;

class CompanyService extends Base
{
    /**
     * SalvarContact: Guarda los datos de un contact en la BD.
     *
     * @author Marcel
     */
    public function SalvarContact(CompanyContactSalvarRequest $d)
    {
        $company_id = $d->company_id;
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $email = (string) ($d->email ?? '');
        $role = (string) ($d->role ?? '');
        $notes = (string) ($d->notes ?? '');
        $em = $this->getDoctrine()->getManager();

        // Verificar name
        $contact = $this->getDoctrine()->getRepository(CompanyContact::class)
           ->findOneBy(['name' => $name, 'company' => $company_id]);
        if (null != $contact) {
            $resultado['success'] = false;
            $resultado['error'] = 'The contact name is in use, please try entering another one.';

            return $resultado;
        }

        $company_entity = $this->getDoctrine()->getRepository(Company::class)
           ->find($company_id);
        if ($company_entity) {
            $entity = new CompanyContact();

            $entity->setName($name);
            $entity->setEmail($email);
            $entity->setPhone($phone);
            $entity->setRole($role);
            $entity->setNotes($notes);

            $entity->setCompany($company_entity);

            $em->persist($entity);

            $em->flush();

            // Salvar log
            $log_operacion = 'Add';
            $log_categoria = 'Company Contact';
            $log_descripcion = "The company contact is added: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['contact_id'] = $entity->getContactId();
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * ActualizarContact: actualiza un contacto existente de la empresa.
     */
    public function ActualizarContact(CompanyContactActualizarRequest $d)
    {
        $contact_id = $d->contact_id;
        $company_id = $d->company_id;
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $email = (string) ($d->email ?? '');
        $role = (string) ($d->role ?? '');
        $notes = (string) ($d->notes ?? '');
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(CompanyContact::class)
            ->find($contact_id);
        if (null === $entity) {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';

            return $resultado;
        }

        $company_entity = $entity->getCompany();
        if (null === $company_entity || (int) $company_entity->getCompanyId() !== (int) $company_id) {
            $resultado['success'] = false;
            $resultado['error'] = 'The company does not match this contact.';

            return $resultado;
        }

        $otro = $this->getDoctrine()->getRepository(CompanyContact::class)
            ->findOneBy(['name' => $name, 'company' => $company_id]);
        if (null !== $otro && (int) $otro->getContactId() !== (int) $contact_id) {
            $resultado['success'] = false;
            $resultado['error'] = 'The contact name is in use, please try entering another one.';

            return $resultado;
        }

        $entity->setName($name);
        $entity->setEmail($email);
        $entity->setPhone($phone);
        $entity->setRole($role);
        $entity->setNotes($notes);

        $em->flush();

        $log_operacion = 'Update';
        $log_categoria = 'Company Contact';
        $log_descripcion = "The company contact is updated: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['contact_id'] = $entity->getContactId();

        return $resultado;
    }

    /**
     * EliminarContact: Elimina un contact en la BD.
     *
     * @author Marcel
     */
    public function EliminarContact(CompanyContactIdRequest $dto)
    {
        $contact_id = $dto->contact_id;
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(CompanyContact::class)
           ->find($contact_id);
        /** @var CompanyContact $entity */
        if (null != $entity) {
            // estimates
            /** @var EstimateCompanyRepository $estimateCompanyRepo */
            $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
            $estimates = $estimateCompanyRepo->ListarEstimatesDeContact($contact_id);
            foreach ($estimates as $estimate) {
                $em->remove($estimate);
            }

            $contact_name = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Contact';
            $log_descripcion = "The company contact is deleted: $contact_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * CargarDatosCompany: Carga los datos de un company.
     *
     * @author Marcel
     */
    public function CargarDatosCompany(CompanyIdRequest $dto)
    {
        $company_id = $dto->company_id;
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(Company::class)
           ->find($company_id);
        /** @var Company $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['address'] = $entity->getAddress();
            $arreglo_resultado['contactName'] = $entity->getContactName();
            $arreglo_resultado['contactEmail'] = $entity->getContactEmail();
            $arreglo_resultado['email'] = $entity->getEmail();
            $arreglo_resultado['website'] = $entity->getWebsite();

            // contacts
            $contacts = $this->ListarContactsDeCompany($company_id);
            $arreglo_resultado['contacts'] = $contacts;

            // projects
            $projects = $this->ListarProjects($company_id);
            $arreglo_resultado['projects'] = $projects;

            $resultado['success'] = true;
            $resultado['company'] = $arreglo_resultado;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * ListarProjects.
     *
     * @return array
     */
    public function ListarProjects($company_id)
    {
        $projects = [];

        /** @var ProjectRepository $projectRepo */
        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
        $company_projects = $projectRepo->ListarProjectsDeCompany($company_id);

        foreach ($company_projects as $key => $value) {
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                'id' => $project_id,
                'projectNumber' => $value->getProjectNumber(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'company' => $value->getCompany()->getName(),
                'county' => $this->getCountiesDescriptionForProject($value),
                'status' => $value->getStatus(),
                'startDate' => '' != $value->getStartDate() ? $value->getStartDate()->format('m/d/Y') : '',
                'endDate' => '' != $value->getEndDate() ? $value->getEndDate()->format('m/d/Y') : '',
                'dueDate' => '' != $value->getDueDate() ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                'posicion' => $key,
            ];
        }

        return $projects;
    }

    /**
     * EliminarCompany: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarCompany(CompanyIdRequest $dto)
    {
        $company_id = $dto->company_id;
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Company::class)
           ->find($company_id);
        /** @var Company $entity */
        if (null != $entity) {
            // projects
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $projects = $projectRepo->ListarProjectsDeCompany($company_id);
            if (count($projects) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The company could not be deleted, because it is related to a project';

                return $resultado;
            }
            $estimates = $this->getDoctrine()->getRepository(Estimate::class)->findBy(['company' => $entity]);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The company could not be deleted, because it is related to an estimate';

                return $resultado;
            }

            // eliminar info
            $this->EliminarInformacionDeCompany($company_id);

            $company_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Company';
            $log_descripcion = "The company is deleted: $company_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarCompanies: Elimina los companies seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarCompanies(CompanyIdsRequest $dto)
    {
        $ids = $dto->ids;
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if (!empty($ids)) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $company_id) {
                if ('' != $company_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Company::class)
                       ->find($company_id);
                    /** @var Company $entity */
                    if (null != $entity) {
                        // projects
                        /** @var ProjectRepository $projectRepo */
                        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
                        $projects = $projectRepo->ListarProjectsDeCompany($company_id);
                        if (0 == count($projects)) {
                            // eliminar info
                            $this->EliminarInformacionDeCompany($company_id);

                            $company_descripcion = $entity->getName();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Company';
                            $log_descripcion = "The company is deleted: $company_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The companies could not be deleted, because they are associated with a project';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected companies because they are associated with a project';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeCompany.
     *
     * @return void
     */
    public function EliminarInformacionDeCompany($company_id)
    {
        $em = $this->getDoctrine()->getManager();

        // contacts
        /** @var CompanyContactRepository $companyContactRepo */
        $companyContactRepo = $this->getDoctrine()->getRepository(CompanyContact::class);
        $contacts = $companyContactRepo->ListarContacts($company_id);
        foreach ($contacts as $contact) {
            // estimates
            /** @var EstimateCompanyRepository $estimateCompanyRepo */
            $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
            $estimates = $estimateCompanyRepo->ListarEstimatesDeContact($contact->getContactId());
            foreach ($estimates as $estimate) {
                $em->remove($estimate);
            }

            $em->remove($contact);
        }

        // estimates
        /** @var EstimateCompanyRepository $estimateCompanyRepo */
        $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
        $estimates = $estimateCompanyRepo->ListarEstimatesDeCompany($company_id);
        foreach ($estimates as $estimate) {
            $em->remove($estimate);
        }
    }

    /**
     * ActualizarCompany: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarCompany(CompanyActualizarRequest $d)
    {
        $company_id = $d->company_id;
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $email = (string) ($d->email ?? '');
        $website = (string) ($d->website ?? '');
        $contacts = $this->decodeContactsJson($d->contacts);
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Company::class)
           ->find($company_id);
        /** @var Company $entity */
        if (null != $entity) {
            // Verificar description
            $company = $this->getDoctrine()->getRepository(Company::class)
               ->findOneBy(['name' => $name]);
            if (null != $company && $entity->getCompanyId() != $company->getCompanyId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The company name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setName($name);
            $entity->setPhone($phone);
            $entity->setAddress($address);
            $entity->setContactName($contactName);
            $entity->setContactEmail($contactEmail);
            $entity->setEmail($email);
            $entity->setWebsite($website);

            $entity->setUpdatedAt(new \DateTime());

            // save contacts
            $this->SalvarContacts($entity, $contacts);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Company';
            $log_descripcion = "The company is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['company_id'] = $entity->getCompanyId();

            return $resultado;
        }

        return ['success' => false, 'error' => 'The requested record does not exist'];
    }

    /**
     * SalvarCompany: Guarda los datos de company en la BD.
     *
     * @author Marcel
     */
    public function SalvarCompany(CompanySalvarRequest $d)
    {
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $email = (string) ($d->email ?? '');
        $website = (string) ($d->website ?? '');
        $contacts = $this->decodeContactsJson($d->contacts);
        $em = $this->getDoctrine()->getManager();

        // Verificar email
        $company = $this->getDoctrine()->getRepository(Company::class)
           ->findOneBy(['name' => $name]);
        if (null != $company) {
            $resultado['success'] = false;
            $resultado['error'] = 'The company name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Company();

        $entity->setName($name);
        $entity->setPhone($phone);
        $entity->setAddress($address);
        $entity->setContactName($contactName);
        $entity->setContactEmail($contactEmail);
        $entity->setEmail($email);
        $entity->setWebsite($website);

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        // save contacts
        $this->SalvarContacts($entity, $contacts);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Company';
        $log_descripcion = "The company is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['company_id'] = $entity->getCompanyId();

        return $resultado;
    }

    /**
     * SalvarContacts.
     *
     * @param Company $entity
     *
     * @return void
     */
    public function SalvarContacts($entity, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        if (!\is_iterable($contacts)) {
            return;
        }

        // Senderos
        foreach ($contacts as $value) {
            $contact_entity = null;

            if (is_numeric($value->contact_id)) {
                $contact_entity = $this->getDoctrine()->getRepository(CompanyContact::class)
                   ->find($value->contact_id);
            }

            $is_new_contact = false;
            if (null == $contact_entity) {
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
     * ListarCompanies: Listar los companies.
     *
     * @author Marcel
     */
    public function ListarCompanies(CompanyListarRequest $listar)
    {
        $dt = $listar->dt;
        /** @var CompanyRepository $companyRepo */
        $companyRepo = $this->getDoctrine()->getRepository(Company::class);
        $resultado = $companyRepo->ListarCompaniesConTotal($dt['start'], $dt['length'], $dt['search'], $dt['orderField'], $dt['orderDir']);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $company_id = $value->getCompanyId();

            $data[] = [
                'id' => $company_id,
                'name' => $value->getName(),
                'phone' => $value->getPhone(),
                'address' => $value->getAddress(),
                'contactName' => $value->getContactName(),
                'contactEmail' => $value->getContactEmail(),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    /**
     * Contactos de la empresa para la acción admin `listarContacts` (DTO).
     *
     * @return array<mixed>
     */
    public function ListarContactsDeCompanyAdmin(CompanyIdRequest $dto): array
    {
        return parent::ListarContactsDeCompany($dto->company_id);
    }

    /**
     * @return list<\stdClass>
     */
    private function decodeContactsJson(?string $contactsJson): array
    {
        if (null === $contactsJson || '' === trim($contactsJson)) {
            return [];
        }
        $decoded = json_decode($contactsJson);
        if (!\is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}
