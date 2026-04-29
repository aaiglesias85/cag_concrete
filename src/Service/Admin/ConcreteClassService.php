<?php

namespace App\Service\Admin;

use App\Dto\Admin\ConcreteClass\ConcreteClassActualizarRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassIdRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassIdsRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassListarRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassSalvarRequest;
use App\Entity\ConcreteClass;
use App\Entity\Project;
use App\Repository\ConcreteClassRepository;
use App\Repository\ProjectRepository;
use App\Service\Base\Base;

class ConcreteClassService extends Base
{
    /**
     * CargarDatos: Carga los datos de un concrete class.
     *
     * @author Marcel
     */
    public function CargarDatos(ConcreteClassIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $concrete_class_id = $dto->concrete_class_id;
        $entity = $this->getDoctrine()->getRepository(ConcreteClass::class)
           ->find($concrete_class_id);
        /** @var ConcreteClass $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['status'] = $entity->getStatus();

            // projects
            $projects = $this->ListarProjects($concrete_class_id);
            $arreglo_resultado['projects'] = $projects;

            $resultado['success'] = true;
            $resultado['class'] = $arreglo_resultado;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarClass: Elimina un concrete class en la BD.
     *
     * @author Marcel
     */
    public function EliminarClass(ConcreteClassIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $concrete_class_id = $dto->concrete_class_id;

        $entity = $this->getDoctrine()->getRepository(ConcreteClass::class)
           ->find($concrete_class_id);
        /** @var ConcreteClass $entity */
        if (null != $entity) {
            // projects
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $projects = $projectRepo->ListarProjectsDeConcreteClass($concrete_class_id);
            if (count($projects) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The concrete class could not be deleted, because it is related to a project';

                return $resultado;
            }

            $class_name = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Concrete Class';
            $log_descripcion = "The concrete class is deleted: $class_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarVarios: Elimina los concrete classes seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarVarios(ConcreteClassIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $concrete_class_id) {
                if ('' != $concrete_class_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(ConcreteClass::class)
                       ->find($concrete_class_id);
                    /** @var ConcreteClass $entity */
                    if (null != $entity) {
                        // projects
                        /** @var ProjectRepository $projectRepo */
                        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
                        $projects = $projectRepo->ListarProjectsDeConcreteClass($concrete_class_id);
                        if (0 === count($projects)) {
                            $class_name = $entity->getName();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Concrete Class';
                            $log_descripcion = "The concrete class is deleted: $class_name";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The concrete classes could not be deleted, because they are associated with a project';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected concrete classes because they are associated with a project';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * Actualizar: Actualiza los datos del concrete class en la BD.
     *
     * @author Marcel
     */
    public function Actualizar(ConcreteClassActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $concrete_class_id = $d->concrete_class_id;
        $name = (string) $d->name;
        $status = (string) $d->status;

        $entity = $this->getDoctrine()->getRepository(ConcreteClass::class)
           ->find($concrete_class_id);
        /** @var ConcreteClass $entity */
        if (null != $entity) {
            // Verificar name
            $class = $this->getDoctrine()->getRepository(ConcreteClass::class)
               ->findOneBy(['name' => $name]);
            if (null != $class && $entity->getConcreteClassId() != $class->getConcreteClassId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The concrete class name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setName($name);
            $entity->setStatus($this->parseBooleanStatus($status));

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Concrete Class';
            $log_descripcion = "The concrete class is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['concrete_class_id'] = $entity->getConcreteClassId();

            return $resultado;
        }
        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * Salvar: Guarda los datos de concrete class en la BD.
     *
     * @author Marcel
     */
    public function Salvar(ConcreteClassSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $name = (string) $d->name;
        $status = (string) $d->status;

        // Verificar name
        $class = $this->getDoctrine()->getRepository(ConcreteClass::class)
           ->findOneBy(['name' => $name]);
        if (null != $class) {
            $resultado['success'] = false;
            $resultado['error'] = 'The concrete class name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new ConcreteClass();

        $entity->setName($name);
        $entity->setStatus($this->parseBooleanStatus($status));

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Concrete Class';
        $log_descripcion = "The concrete class is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['concrete_class_id'] = $entity->getConcreteClassId();

        return $resultado;
    }

    /**
     * Listar: Listar los concrete classes.
     *
     * @author Marcel
     */
    public function Listar(ConcreteClassListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var ConcreteClassRepository $concreteClassRepo */
        $concreteClassRepo = $this->getDoctrine()->getRepository(ConcreteClass::class);
        $resultado = $concreteClassRepo->ListarConcreteClassesConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $concrete_class_id = $value->getConcreteClassId();

            $data[] = [
                'id' => $concrete_class_id,
                'name' => $value->getName(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    /**
     * ListarProjects.
     *
     * @return array
     */
    public function ListarProjects($concrete_class_id)
    {
        $projects = [];

        /** @var ProjectRepository $projectRepo */
        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
        $class_projects = $projectRepo->ListarProjectsDeConcreteClass($concrete_class_id);

        foreach ($class_projects as $key => $value) {
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                'id' => $project_id,
                'projectNumber' => $value->getProjectNumber(),
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

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
