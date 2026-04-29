<?php

namespace App\Service\Admin;

use App\Dto\Admin\Equation\EquationActualizarRequest;
use App\Dto\Admin\Equation\EquationIdRequest;
use App\Dto\Admin\Equation\EquationIdsRequest;
use App\Dto\Admin\Equation\EquationListarRequest;
use App\Dto\Admin\Equation\EquationSalvarPayItemsRequest;
use App\Dto\Admin\Equation\EquationSalvarRequest;
use App\Constants\FunctionId;
use App\Entity\Equation;
use App\Entity\EstimateQuoteItem;
use App\Entity\Item;
use App\Entity\ProjectItem;
use App\Entity\Usuario;
use App\Repository\EquationRepository;
use App\Repository\EstimateQuoteItemRepository;
use App\Repository\ItemRepository;
use App\Repository\ProjectItemRepository;
use App\Service\Base\Base;

class EquationService extends Base
{
    /**
     * SalvarPayItems: Guarda los datos en la BD.
     *
     * @author Marcel
     */
    public function SalvarPayItems(EquationSalvarPayItemsRequest $d)
    {
        $raw = (string) ($d->pay_items ?? '');
        $pay_items = json_decode($raw);
        if (!\is_array($pay_items)) {
            return [
                'success' => false,
                'error' => 'Invalid pay_items JSON',
            ];
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($pay_items as $pay_item) {
            $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
               ->find($pay_item->project_item_id);

            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($pay_item->equation_id);

            if (null != $project_item_entity && null != $equation_entity) {
                $project_item_entity->setEquation($equation_entity);
            }
        }

        $em->flush();

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarPayItems: Lista los pay items.
     *
     * @author Marcel
     */
    public function ListarPayItems(EquationIdsRequest $dto)
    {
        $items = [];

        $ids = explode(',', (string) ($dto->ids ?? ''));
        foreach ($ids as $id) {
            $project_items = $this->ListarItemsDeProject($id);
            $items = array_merge($items, $project_items);
        }

        return $items;
    }

    /**
     * CargarDatosEquation: Carga los datos de un equation.
     *
     * @author Marcel
     */
    public function CargarDatosEquation(EquationIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $equation_id = $dto->equation_id;
        $entity = $this->getDoctrine()->getRepository(Equation::class)
           ->find($equation_id);
        /** @var Equation $entity */
        if (null != $entity) {
            $arreglo_resultado['descripcion'] = $entity->getDescription();
            $arreglo_resultado['equation'] = $entity->getEquation();
            $arreglo_resultado['status'] = $entity->getStatus();

            // items
            $items = $this->ListarItemsDeProject($equation_id);
            $arreglo_resultado['items'] = $items;

            $resultado['success'] = true;
            $resultado['equation'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarItemsDeProject.
     *
     * @return array
     */
    public function ListarItemsDeProject($equation_id)
    {
        $items = [];

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $lista = $projectItemRepo->ListarProjectItemsDeEquation($equation_id);
        foreach ($lista as $key => $value) {
            $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

            $quantity = $value->getQuantity();
            $price = $value->getPrice();
            $total = $quantity * $price;

            $items[] = [
                'project_item_id' => $value->getId(),
                'item_id' => $value->getItem()->getItemId(),
                'item' => $value->getItem()->getName(),
                'unit' => null != $value->getItem()->getUnit() ? $value->getItem()->getUnit()->getDescription() : '',
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total,
                'yield_calculation' => $value->getYieldCalculation(),
                'yield_calculation_name' => $yield_calculation_name,
                'equation_id' => null != $value->getEquation() ? $value->getEquation()->getEquationId() : '',
                'project_id' => $value->getProject()->getProjectId(),
                'project' => $value->getProject()->getProjectNumber().' - '.$value->getProject()->getDescription(),
                'posicion' => $key,
            ];
        }

        return $items;
    }

    /**
     * EliminarEquation: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarEquation(EquationIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $equation_id = $dto->equation_id;

        $entity = $this->getDoctrine()->getRepository(Equation::class)
           ->find($equation_id);
        /** @var Equation $entity */
        if (null != $entity) {
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $project_items = $projectItemRepo->ListarProjectItemsDeEquation($equation_id);
            if (count($project_items) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The equation could not be deleted, because it is related to a item';
                $resultado['equation_ids_con_items'] = [$equation_id];

                return $resultado;
            }

            // items
            /** @var ItemRepository $itemRepo */
            $itemRepo = $this->getDoctrine()->getRepository(Item::class);
            $items = $itemRepo->ListarItemsDeEquation((string) $equation_id);
            foreach ($items as $item) {
                $item->setYieldCalculation(null);
                $item->setEquation(null);
            }

            // estimate quote items
            /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
            $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
            $estimate_quote_items = $estimateQuoteItemRepo->ListarEstimateQuoteItemsDeEquation($equation_id);
            foreach ($estimate_quote_items as $estimate_quote_item) {
                $estimate_quote_item->setYieldCalculation(null);
                $estimate_quote_item->setEquation(null);
            }

            $equation_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Equation';
            $log_descripcion = "The equation is deleted: $equation_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarEquations: Elimina los equations seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarEquations(EquationIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $equation_ids_con_items = [];

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $equation_id) {
                if ('' != $equation_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Equation::class)
                       ->find($equation_id);
                    /** @var Equation $entity */
                    if (null != $entity) {
                        /** @var ProjectItemRepository $projectItemRepo */
                        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
                        $project_items = $projectItemRepo->ListarProjectItemsDeEquation($equation_id);
                        if (0 == count($project_items)) {
                            // items
                            /** @var ItemRepository $itemRepo */
                            $itemRepo = $this->getDoctrine()->getRepository(Item::class);
                            $items = $itemRepo->ListarItemsDeEquation((string) $equation_id);
                            foreach ($items as $item) {
                                $item->setYieldCalculation(null);
                                $item->setEquation(null);
                            }

                            // estimate quote items
                            /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
                            $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
                            $estimate_quote_items = $estimateQuoteItemRepo->ListarEstimateQuoteItemsDeEquation($equation_id);
                            foreach ($estimate_quote_items as $estimate_quote_item) {
                                $estimate_quote_item->setYieldCalculation(null);
                                $estimate_quote_item->setEquation(null);
                            }

                            $equation_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Equation';
                            $log_descripcion = "The equation is deleted: $equation_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        } else {
                            $equation_ids_con_items[] = $equation_id;
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The equations could not be deleted, because they are associated with a item';
            $resultado['equation_ids_con_items'] = $equation_ids_con_items;
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected equations because they are associated with a item';
            $resultado['message'] = $mensaje;
            $resultado['equation_ids_con_items'] = $equation_ids_con_items;
        }

        return $resultado;
    }

    /**
     * ActualizarEquation: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarEquation(EquationActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $equation_id = $d->equation_id;
        $description = (string) $d->description;
        $equation = (string) $d->equation;
        $status = (string) $d->status;

        $entity = $this->getDoctrine()->getRepository(Equation::class)
           ->find($equation_id);
        /** @var Equation $entity */
        if (null != $entity) {
            // Verificar description
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)
               ->findOneBy(['description' => $description]);
            if (null != $equation_entity && $entity->getEquationId() != $equation_entity->getEquationId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The equation name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setEquation($equation);
            $entity->setStatus($this->parseBooleanStatus($status));

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Equation';
            $log_descripcion = "The equation is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['equation_id'] = $equation_id;

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarEquation: Guarda los datos de equation en la BD.
     *
     * @author Marcel
     */
    public function SalvarEquation(EquationSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $description = (string) $d->description;
        $equation = (string) $d->equation;
        $status = (string) $d->status;

        // Verificar description
        $equation_entity = $this->getDoctrine()->getRepository(Equation::class)
           ->findOneBy(['description' => $description]);
        if (null != $equation_entity) {
            $resultado['success'] = false;
            $resultado['error'] = 'The equation name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Equation();

        $entity->setDescription($description);
        $entity->setEquation($equation);
        $entity->setStatus($this->parseBooleanStatus($status));

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Equation';
        $log_descripcion = "The equation is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['equation_id'] = $entity->getEquationId();

        return $resultado;
    }

    /**
     * ListarEquations: Listar los equations.
     *
     * @author Marcel
     */
    public function ListarEquations(EquationListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var EquationRepository $equationRepo */
        $equationRepo = $this->getDoctrine()->getRepository(Equation::class);
        $resultado = $equationRepo->ListarEquationsConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $equation_id = $value->getEquationId();

            $acciones = $this->ListarAcciones($equation_id);

            $data[] = [
                'id' => $equation_id,
                'description' => $value->getDescription(),
                'equation' => $value->getEquation(),
                'status' => $value->getStatus() ? 1 : 0,
                'acciones' => $acciones,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    /**
     * TotalEquations: Total de equations.
     *
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalEquations($sSearch)
    {
        /** @var EquationRepository $equationRepo */
        $equationRepo = $this->getDoctrine()->getRepository(Equation::class);
        $total = $equationRepo->TotalEquations($sSearch);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD.
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return '';
        }
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), FunctionId::EQUATION);

        $acciones = '';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="'.$id.'"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="'.$id.'"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill"
                 title="Delete record" data-id="'.$id.'"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
