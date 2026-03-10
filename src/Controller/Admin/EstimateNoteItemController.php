<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\EstimateNoteItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EstimateNoteItemController extends AbstractController
{
    private EstimateNoteItemService $estimateNoteItemService;

    public function __construct(EstimateNoteItemService $estimateNoteItemService)
    {
        $this->estimateNoteItemService = $estimateNoteItemService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->estimateNoteItemService->BuscarPermiso($usuario->getUsuarioId(), 38);
        if (count($permiso) > 0 && $permiso[0]['ver']) {
            return $this->render('admin/estimate-note-item/index.html.twig', [
                'permiso' => $permiso[0],
            ]);
        }
        return $this->redirectToRoute('denegado');
    }

    public function listar(Request $request)
    {
        try {
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'description'],
                defaultOrderField: 'description'
            );

            $result = $this->estimateNoteItemService->ListarItems(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
            );

            return $this->json([
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function salvar(Request $request)
    {
        $id = $request->get('id');
        $description = $request->get('description');

        try {
            if ($id === '' || $id === null) {
                $resultado = $this->estimateNoteItemService->Salvar($description);
            } else {
                $resultado = $this->estimateNoteItemService->Actualizar($id, $description);
            }

            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'id' => $resultado['id'],
                    'message' => 'The operation was successful',
                ]);
            }
            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function eliminar(Request $request)
    {
        $id = $request->get('id');
        try {
            $resultado = $this->estimateNoteItemService->Eliminar($id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }
            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->get('ids');
        try {
            $resultado = $this->estimateNoteItemService->EliminarVarios($ids);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => $resultado['message']]);
            }
            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function cargarDatos(Request $request)
    {
        $id = $request->get('id');
        try {
            $resultado = $this->estimateNoteItemService->CargarDatos($id);
            if (!empty($resultado['success']) && $resultado['success']) {
                return $this->json(['success' => true, 'item' => $resultado['item']]);
            }
            return $this->json(['success' => false, 'error' => 'Record not found']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
