<?php

namespace App\Utils\Admin;

use App\Entity\EstimateNoteItem;
use App\Repository\EstimateNoteItemRepository;
use App\Utils\Base;

class EstimateNoteItemService extends Base
{
    /**
     * CargarDatos: Carga los datos de un estimate note item.
     */
    public function CargarDatos($id): array
    {
        $resultado = [];

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
    public function Eliminar($id): array
    {
        $em = $this->getDoctrine()->getManager();
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
    public function EliminarVarios($ids): array
    {
        $em = $this->getDoctrine()->getManager();
        $cant_eliminada = 0;
        $cant_total = 0;

        if ('' !== $ids) {
            $ids = explode(',', (string) $ids);
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
    public function Actualizar($id, $description, $type = 'item'): array
    {
        $type = $this->normalizeType($type);
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
     *
     * @param string $type 'item' o 'template'; si se omite o está vacío se usa 'item' (ej. creación desde modal de ítem)
     */
    public function Salvar($description, $type = 'item'): array
    {
        $type = $this->normalizeType($type);
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
    public function ListarItems($start, $limit, $sSearch, $orderField, $orderDir): array
    {
        /** @var EstimateNoteItemRepository $repo */
        $repo = $this->getDoctrine()->getRepository(EstimateNoteItem::class);
        $resultado = $repo->ListarConTotal($start, $limit, $sSearch, $orderField, $orderDir);

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
