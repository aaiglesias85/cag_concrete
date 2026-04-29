<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de contacts[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarContactsDeProject}.
 */
final readonly class ProjectContactRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $contact_id,
        public mixed $company_contact_id,
        public string $name,
        public string $email,
        public string $phone,
        public string $role,
        public string $notes,
        public int $posicion,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['contact_id'] ?? null,
            $row['company_contact_id'] ?? null,
            (string) ($row['name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['phone'] ?? ''),
            (string) ($row['role'] ?? ''),
            (string) ($row['notes'] ?? ''),
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
        );
    }

    /**
     * @return array{
     *     contact_id: mixed,
     *     company_contact_id: mixed,
     *     name: string,
     *     email: string,
     *     phone: string,
     *     role: string,
     *     notes: string,
     *     posicion: int
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'contact_id' => $this->contact_id,
            'company_contact_id' => $this->company_contact_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'notes' => $this->notes,
            'posicion' => $this->posicion,
        ];
    }
}
