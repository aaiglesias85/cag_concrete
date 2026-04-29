<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de prevailing_roles dentro del detalle de proyecto (API app).
 *
 * @phpstan-type PrevailingRoleWire array{
 *     county_id: mixed,
 *     county_description: mixed,
 *     role_id: mixed,
 *     role_description: mixed,
 *     rate: mixed
 * }
 */
final readonly class PrevailingRoleRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $county_id,
        public mixed $county_description,
        public mixed $role_id,
        public mixed $role_description,
        public mixed $rate,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['county_id'] ?? '',
            $row['county_description'] ?? '',
            $row['role_id'] ?? '',
            $row['role_description'] ?? '',
            $row['rate'] ?? '',
        );
    }

    /**
     * @return PrevailingRoleWire
     */
    public function jsonSerialize(): array
    {
        return [
            'county_id' => $this->county_id,
            'county_description' => $this->county_description,
            'role_id' => $this->role_id,
            'role_description' => $this->role_description,
            'rate' => $this->rate,
        ];
    }
}
