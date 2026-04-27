<?php

namespace App\Service\Base;

use App\Entity\CompanyContact;
use App\Entity\ConcreteVendorContact;
use App\Entity\ProjectContact;
use Doctrine\Persistence\ManagerRegistry;

class BaseContactListingService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * Compatible with project_contact.company_contact_id NULL (legacy records).
     *
     * @return array<int, array<string, mixed>>
     */
    public function ListarContactsDeProject($project_id): array
    {
        $contacts = [];

        /** @var \App\Repository\ProjectContactRepository $projectContactRepo */
        $projectContactRepo = $this->doctrine->getRepository(ProjectContact::class);
        $project_contacts = $projectContactRepo->ListarContacts($project_id);
        foreach ($project_contacts as $key => $contact) {
            $companyContact = $contact->getCompanyContact();
            $contacts[] = [
                'contact_id' => $contact->getContactId(),
                'company_contact_id' => $companyContact ? $companyContact->getContactId() : null,
                'name' => $contact->getName() ?? '',
                'email' => $contact->getEmail() ?? '',
                'phone' => $contact->getPhone() ?? '',
                'role' => $contact->getRole() ?? '',
                'notes' => $contact->getNotes() ?? '',
                'posicion' => $key,
            ];
        }

        return $contacts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarContactsDeConcreteVendor($vendor_id): array
    {
        $contacts = [];

        /** @var \App\Repository\ConcreteVendorContactRepository $concreteVendorContactRepo */
        $concreteVendorContactRepo = $this->doctrine->getRepository(ConcreteVendorContact::class);
        $vendor_contacts = $concreteVendorContactRepo->ListarContacts($vendor_id);
        foreach ($vendor_contacts as $key => $contact) {
            $contacts[] = [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'role' => $contact->getRole(),
                'notes' => $contact->getNotes(),
                'posicion' => $key,
            ];
        }

        return $contacts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarContactsDeCompany($company_id): array
    {
        $contacts = [];

        /** @var \App\Repository\CompanyContactRepository $companyContactRepo */
        $companyContactRepo = $this->doctrine->getRepository(CompanyContact::class);
        $company_contacts = $companyContactRepo->ListarContacts($company_id);
        foreach ($company_contacts as $key => $contact) {
            $contacts[] = [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'role' => $contact->getRole(),
                'notes' => $contact->getNotes(),
                'posicion' => $key,
            ];
        }

        return $contacts;
    }
}
