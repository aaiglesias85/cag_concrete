<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de data_tracking[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarDataTrackings} (`data`).
 */
final readonly class ProjectDataTrackingRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public string $project,
        public string $date,
        public mixed $stationNumber,
        public mixed $measuredBy,
        public mixed $totalConcUsed,
        public mixed $lostConcrete,
        public mixed $concVendor,
        public mixed $concPrice,
        public string $inspector,
        public string $inspectorNumber,
        public mixed $crewLead,
        public mixed $notes,
        public mixed $totalLabor,
        public mixed $totalMaterial,
        public mixed $totalStamps,
        public mixed $otherMaterials,
        public string $leads,
        public mixed $totalPeople,
        public mixed $overheadPrice,
        public mixed $totalOverhead,
        public mixed $colorUsed,
        public mixed $colorPrice,
        public mixed $totalColor,
        public mixed $total_concrete_yiel,
        public mixed $total_quantity_today,
        public mixed $total_daily_today,
        public mixed $total_concrete,
        public mixed $profit,
        public int $pending,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            (string) ($row['project'] ?? ''),
            (string) ($row['date'] ?? ''),
            $row['stationNumber'] ?? null,
            $row['measuredBy'] ?? null,
            $row['totalConcUsed'] ?? null,
            $row['lostConcrete'] ?? null,
            $row['concVendor'] ?? null,
            $row['concPrice'] ?? null,
            (string) ($row['inspector'] ?? ''),
            (string) ($row['inspectorNumber'] ?? ''),
            $row['crewLead'] ?? null,
            $row['notes'] ?? null,
            $row['totalLabor'] ?? null,
            $row['totalMaterial'] ?? null,
            $row['totalStamps'] ?? null,
            $row['otherMaterials'] ?? null,
            (string) ($row['leads'] ?? ''),
            $row['totalPeople'] ?? null,
            $row['overheadPrice'] ?? null,
            $row['totalOverhead'] ?? null,
            $row['colorUsed'] ?? null,
            $row['colorPrice'] ?? null,
            $row['totalColor'] ?? null,
            $row['total_concrete_yiel'] ?? null,
            $row['total_quantity_today'] ?? null,
            $row['total_daily_today'] ?? null,
            $row['total_concrete'] ?? null,
            $row['profit'] ?? null,
            isset($row['pending']) ? (int) $row['pending'] : 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'project' => $this->project,
            'date' => $this->date,
            'stationNumber' => $this->stationNumber,
            'measuredBy' => $this->measuredBy,
            'totalConcUsed' => $this->totalConcUsed,
            'lostConcrete' => $this->lostConcrete,
            'concVendor' => $this->concVendor,
            'concPrice' => $this->concPrice,
            'inspector' => $this->inspector,
            'inspectorNumber' => $this->inspectorNumber,
            'crewLead' => $this->crewLead,
            'notes' => $this->notes,
            'totalLabor' => $this->totalLabor,
            'totalMaterial' => $this->totalMaterial,
            'totalStamps' => $this->totalStamps,
            'otherMaterials' => $this->otherMaterials,
            'leads' => $this->leads,
            'totalPeople' => $this->totalPeople,
            'overheadPrice' => $this->overheadPrice,
            'totalOverhead' => $this->totalOverhead,
            'colorUsed' => $this->colorUsed,
            'colorPrice' => $this->colorPrice,
            'totalColor' => $this->totalColor,
            'total_concrete_yiel' => $this->total_concrete_yiel,
            'total_quantity_today' => $this->total_quantity_today,
            'total_daily_today' => $this->total_daily_today,
            'total_concrete' => $this->total_concrete,
            'profit' => $this->profit,
            'pending' => $this->pending,
        ];
    }
}
