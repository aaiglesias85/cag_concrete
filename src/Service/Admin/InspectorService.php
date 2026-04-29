<?php

namespace App\Service\Admin;

use App\Dto\Admin\Inspector\InspectorActualizarRequest;
use App\Dto\Admin\Inspector\InspectorIdRequest;
use App\Dto\Admin\Inspector\InspectorIdsRequest;
use App\Dto\Admin\Inspector\InspectorListarRequest;
use App\Dto\Admin\Inspector\InspectorSalvarRequest;
use App\Entity\DataTracking;
use App\Entity\Inspector;
use App\Entity\Project;
use App\Repository\DataTrackingRepository;
use App\Repository\InspectorRepository;
use App\Repository\ProjectRepository;
use App\Service\Base\Base;

class InspectorService extends Base
{
    /**
     * CargarDatosInspector: Carga los datos de un inspector.
     *
     * @author Marcel
     */
    public function CargarDatosInspector(InspectorIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $inspector_id = $dto->inspector_id;
        $entity = $this->getDoctrine()->getRepository(Inspector::class)
           ->find($inspector_id);
        /** @var Inspector $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['email'] = $entity->getEmail();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['status'] = $entity->getStatus();

            // projects
            $projects = $this->ListarProjects($inspector_id);
            $arreglo_resultado['projects'] = $projects;

            $resultado['success'] = true;
            $resultado['inspector'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarProjects.
     *
     * @return array
     */
    public function ListarProjects($inspector_id)
    {
        $projects = [];

        /** @var DataTrackingRepository $dataTrackingRepo */
        $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
        $inspector_projects = $dataTrackingRepo->ListarProjectsDeInspector($inspector_id);

        foreach ($inspector_projects as $key => $inspector_project) {
            $value = $inspector_project->getProject();
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                'id' => $project_id,
                'project_id' => $project_id,
                'projectNumber' => $value->getProjectNumber(),
                'number' => $value->getProjectNumber(),
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
     * EliminarInspector: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarInspector(InspectorIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $inspector_id = $dto->inspector_id;

        $entity = $this->getDoctrine()->getRepository(Inspector::class)
           ->find($inspector_id);
        /** @var Inspector $entity */
        if (null != $entity) {
            // projects
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $projects = $projectRepo->ListarProjectsDeInspector($inspector_id);
            if (count($projects) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The inspector could not be deleted, because it is related to a project';

                return $resultado;
            }

            // data tracking
            /** @var DataTrackingRepository $dataTrackingRepo */
            $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
            $data_tracking = $dataTrackingRepo->ListarDataTrackingsDeInspector($inspector_id);
            if (count($data_tracking) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The inspector could not be deleted, because it is related to a data tracking';

                return $resultado;
            }

            $inspector_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Inspector';
            $log_descripcion = "The inspector is deleted: $inspector_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarInspectors: Elimina los inspectors seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarInspectors(InspectorIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $inspector_id) {
                if ('' != $inspector_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Inspector::class)
                       ->find($inspector_id);
                    /** @var Inspector $entity */
                    if (null != $entity) {
                        // projects
                        /** @var ProjectRepository $projectRepo */
                        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
                        $projects = $projectRepo->ListarProjectsDeInspector($inspector_id);
                        // data tracking
                        /** @var DataTrackingRepository $dataTrackingRepo */
                        $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
                        $data_tracking = $dataTrackingRepo->ListarDataTrackingsDeInspector($inspector_id);

                        if (0 == count($projects) && 0 == count($data_tracking)) {
                            $inspector_descripcion = $entity->getName();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Inspector';
                            $log_descripcion = "The inspector is deleted: $inspector_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The inspectors could not be deleted, because they are associated with a project or data tracking';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected inspectors because they are associated with a project or data tracking';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarInspector: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarInspector(InspectorActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $inspector_id = $d->inspector_id;
        $name = (string) $d->name;
        $email = (string) $d->email;
        $phone = (string) ($d->phone ?? '');
        $status = (string) $d->status;

        $entity = $this->getDoctrine()->getRepository(Inspector::class)
           ->find($inspector_id);
        /** @var Inspector $entity */
        if (null != $entity) {
            // Verificar description
            if ('' != $email) {
                $inspector = $this->getDoctrine()->getRepository(Inspector::class)
                   ->findOneBy(['email' => $email]);
                if (null != $inspector && $entity->getInspectorId() != $inspector->getInspectorId()) {
                    $resultado['success'] = false;
                    $resultado['error'] = 'The inspector email is in use, please try entering another one.';

                    return $resultado;
                }
            }

            $entity->setName($name);
            $entity->setEmail($email);
            $entity->setPhone($phone);
            $entity->setStatus($this->parseBooleanStatus($status));

            $entity->setUpdatedAt(new \DateTime());

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Inspector';
            $log_descripcion = "The inspector is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['inspector_id'] = $inspector_id;

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarInspector: Guarda los datos de inspector en la BD.
     *
     * @author Marcel
     */
    public function SalvarInspector(InspectorSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $name = (string) $d->name;
        $email = (string) $d->email;
        $phone = (string) ($d->phone ?? '');
        $status = (string) $d->status;

        // Verificar email
        if ('' != $email) {
            $inspector = $this->getDoctrine()->getRepository(Inspector::class)
               ->findOneBy(['email' => $email]);
            if (null != $inspector) {
                $resultado['success'] = false;
                $resultado['error'] = 'The inspector email is in use, please try entering another one.';

                return $resultado;
            }
        }

        $entity = new Inspector();

        $entity->setName($name);
        $entity->setEmail($email);
        $entity->setPhone($phone);
        $entity->setStatus($this->parseBooleanStatus($status));

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Inspector';
        $log_descripcion = "The inspector is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['inspector_id'] = $entity->getInspectorId();

        return $resultado;
    }

    /**
     * ListarInspectors: Listar los inspectors.
     *
     * @author Marcel
     */
    public function ListarInspectors(InspectorListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var InspectorRepository $inspectorRepo */
        $inspectorRepo = $this->getDoctrine()->getRepository(Inspector::class);
        $resultado = $inspectorRepo->ListarInspectorsConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $inspector_id = $value->getInspectorId();

            $data[] = [
                'id' => $inspector_id,
                'name' => $value->getName(),
                'email' => $value->getEmail(),
                'phone' => $value->getPhone(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
