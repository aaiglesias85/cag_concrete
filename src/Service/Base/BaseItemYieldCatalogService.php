<?php

namespace App\Service\Base;

use App\Entity\EstimateQuoteItem;
use App\Entity\Item;
use App\Entity\ProjectItem;
use App\Entity\ProjectNotes;
use App\Entity\Unit;
use Doctrine\Persistence\ManagerRegistry;

class BaseItemYieldCatalogService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly BaseDateFormatService $dateFormat,
    ) {
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function ListarYieldsCalculation(): array
    {
        return [
            [
                'id' => 'none',
                'name' => 'NONE',
            ],
            [
                'id' => 'same',
                'name' => 'SAME AS QUANTITY',
            ],
            [
                'id' => 'equation',
                'name' => 'EQUATION',
            ],
        ];
    }

    public function BuscarYieldCalculation($id): string
    {
        $name = '';

        $lista = $this->ListarYieldsCalculation();
        foreach ($lista as $value) {
            if ($value['id'] == $id) {
                $name = $value['name'];
                break;
            }
        }

        return $name;
    }

    /**
     * @param ProjectItem|EstimateQuoteItem $item_entity
     */
    public function DevolverYieldCalculationDeItemProject($item_entity): string
    {
        $yield_calculation = $item_entity->getYieldCalculation();

        $yield_calculation_name = $this->BuscarYieldCalculation($yield_calculation);

        if ('equation' == $yield_calculation && null != $item_entity->getEquation()) {
            $yield_calculation_name = $item_entity->getEquation()->getEquation();
        }

        return $yield_calculation_name;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function ListarUltimaNotaDeProject($project_id): ?array
    {
        $nota = null;

        /** @var \App\Repository\ProjectNotesRepository $projectNotesRepo */
        $projectNotesRepo = $this->doctrine->getRepository(ProjectNotes::class);
        $lista = $projectNotesRepo->ListarNotesDeProject($project_id);
        foreach ($lista as $value) {
            $id = $value->getId();

            $notes = strip_tags($value->getNotes());
            $notes = json_encode($notes);

            $nota = [
                'id' => $id,
                'nota' => $this->dateFormat->truncate($notes, 50),
                'date' => $value->getDate()->format('m/d/Y'),
            ];
            break;
        }

        return $nota;
    }

    /**
     * @return Item
     */
    public function AgregarNewItem($value, $equation_entity)
    {
        $em = $this->doctrine->getManager();

        $item_entity = new Item();

        $item_entity->setName($value->item);
        $item_entity->setDescription((string) ($value->item ?? ''));
        $item_entity->setPrice($value->price);
        $item_entity->setStatus(true);
        $item_entity->setYieldCalculation($value->yield_calculation);

        if (isset($value->bond)) {
            $item_entity->setBond(1 == $value->bond || '1' === $value->bond || true === $value->bond);
        }

        if ('' != $value->unit_id) {
            $unit = $this->doctrine->getRepository(Unit::class)->find($value->unit_id);
            $item_entity->setUnit($unit);
        }

        $item_entity->setEquation($equation_entity);

        $item_entity->setCreatedAt(new \DateTime());

        $em->persist($item_entity);

        $em->flush();

        return $item_entity;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarTodosItems(): array
    {
        $items = [];

        /** @var \App\Repository\ItemRepository $itemRepo */
        $itemRepo = $this->doctrine->getRepository(Item::class);
        $lista = $itemRepo->ListarOrdenados();
        foreach ($lista as $value) {
            $item = $this->DevolverItem($value);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function DevolverItem(Item $value): array
    {
        $yield_calculation_name = $this->DevolverYieldCalculationDeItem($value);

        return [
            'item_id' => $value->getItemId(),
            'item' => $value->getName(),
            'unit' => null != $value->getUnit() ? $value->getUnit()->getDescription() : '',
            'yield_calculation' => $value->getYieldCalculation(),
            'yield_calculation_name' => $yield_calculation_name,
            'equation_id' => null != $value->getEquation() ? $value->getEquation()->getEquationId() : '',
        ];
    }

    public function DevolverYieldCalculationDeItem(Item $item_entity): string
    {
        $yield_calculation = $item_entity->getYieldCalculation();

        $yield_calculation_name = $this->BuscarYieldCalculation($yield_calculation);

        if ('equation' == $yield_calculation && null != $item_entity->getEquation()) {
            $yield_calculation_name = $item_entity->getEquation()->getEquation();
        }

        return $yield_calculation_name;
    }
}
