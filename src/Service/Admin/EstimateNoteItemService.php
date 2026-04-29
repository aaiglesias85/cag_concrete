<?php

namespace App\Service\Admin;

use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemActualizarRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdsRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemListarRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemSalvarRequest;
use App\Entity\EstimateNoteItem;
use App\Repository\EstimateNoteItemRepository;
use App\Service\Base\Base;

class EstimateNoteItemService extends Base
{
    /**
     * CargarDatos: Carga los datos de un estimate note item.
     */
    public function CargarDatos(EstimateNoteItemIdRequest $dto): array
    {
        $resultado = [];

        $id = $dto->id;
        $entity = $this->getDoctrine()->getRepository(EstimateNoteItem::class)->find($id);
        if (null !== $entity) {
            $resultado['success'] = true;
            $resultado['item'] = [
                'description' => $entity->getDescription(),
                'type' => $entity->getType(),
            ];
        }

        return $resultado;
    }

    /**
     * Eliminar: Elimina un estimate note item en la BD.
     */
    public function Eliminar(EstimateNoteItemIdRequest $dto): array
    {
        $em = $this->getDoctrine()->getManager();
        $id = $dto->id;
        $entity = $this->getDoctrine()->getRepository(EstimateNoteItem::class)->find($id);

        if (null !== $entity) {
            $description = $entity->getDescription();
            $em->remove($entity);
            $em->flush();

            $this->SalvarLog('Delete', 'Estimate Note Item', "The estimate note item is deleted: $description");

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'The requested record does not exist'];
    }

    /**
     * EliminarVarios: Elimina los estimate note items seleccionados.
     */
    public function EliminarVarios(EstimateNoteItemIdsRequest $dto): array
    {
        $em = $this->getDoctrine()->getManager();
        $cant_eliminada = 0;
        $cant_total = 0;

        $ids = (string) ($dto->ids ?? '');
        if ('' !== $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $id) {
                $id = trim($id);
                if ('' === $id) {
                    continue;
                }
                ++$cant_total;
                $entity = $this->getDoctrine()->getRepository(EstimateNoteItem::class)->find($id);
                if (null !== $entity) {
                    $description = $entity->getDescription();
                    $em->remove($entity);
                    ++$cant_eliminada;
                    $this->SalvarLog('Delete', 'Estimate Note Item', "The estimate note item is deleted: $description");
                }
            }
            $em->flush();
        }

        if (0 === $cant_eliminada) {
            return ['success' => false, 'error' => 'No records could be deleted.'];
        }

        $message = $cant_eliminada === $cant_total
            ? 'The operation was successful'
            : "The operation was successful. $cant_eliminada of $cant_total record(s) were deleted.";

        return ['success' => true, 'message' => $message];
    }

    /**
     * Actualizar: Actualiza los datos del estimate note item en la BD.
     */
    public function Actualizar(EstimateNoteItemActualizarRequest $d): array
    {
        $id = $d->id;
        $description = (string) $d->description;
        $type = $this->normalizeType($d->type ?? 'item');
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository(EstimateNoteItem::class)->find($id);

        if (null !== $entity) {
            $entity->setDescription($description);
            $entity->setType($type);
            $em->flush();

            $this->SalvarLog('Update', 'Estimate Note Item', "The estimate note item is modified: $description");

            return ['success' => true, 'id' => $entity->getId()];
        }

        return ['success' => false, 'error' => 'The requested record does not exist'];
    }

    /**
     * Salvar: Guarda un nuevo estimate note item en la BD.
     * El tipo (`item`/`template`) se normaliza desde `$d->type`; si se omite o está vacío se usa `item`.
     */
    public function Salvar(EstimateNoteItemSalvarRequest $d): array
    {
        $description = (string) $d->description;
        $type = $this->normalizeType($d->type ?? 'item');
        $em = $this->getDoctrine()->getManager();

        $entity = new EstimateNoteItem();
        $entity->setDescription($description);
        $entity->setType($type);
        $em->persist($entity);
        $em->flush();

        $this->SalvarLog('Add', 'Estimate Note Item', "The estimate note item is added: $description");

        return ['success' => true, 'id' => $entity->getId()];
    }

    private function normalizeType(?string $type): string
    {
        $type = null === $type || '' === $type ? 'item' : strtolower(trim($type));

        return in_array($type, ['item', 'template'], true) ? $type : 'item';
    }

    /**
     * ListarItems: Lista los estimate note items con paginación.
     */
    public function ListarItems(EstimateNoteItemListarRequest $listar): array
    {
        $dt = $listar->dt;

        /** @var EstimateNoteItemRepository $repo */
        $repo = $this->getDoctrine()->getRepository(EstimateNoteItem::class);
        $resultado = $repo->ListarConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];
        foreach ($resultado['data'] as $value) {
            $data[] = [
                'id' => $value->getId(),
                'description' => $value->getDescription(),
                'type' => $value->getType(),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'],
        ];
    }
}
