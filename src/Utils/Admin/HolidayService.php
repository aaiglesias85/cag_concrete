<?php

namespace App\Utils\Admin;

use App\Entity\Holiday;

use App\Utils\Base;

class HolidayService extends Base
{

    /**
     * CargarDatosHoliday: Carga los datos de un holiday
     *
     * @param int $holiday_id Id
     *
     * @author Marcel
     */
    public function CargarDatosHoliday($holiday_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Holiday::class)
            ->find($holiday_id);
        /** @var Holiday $entity */
        if ($entity != null) {

            $arreglo_resultado['day'] = $entity->getDay() != '' ? $entity->getDay()->format('m/d/Y') : '';
            $arreglo_resultado['description'] = $entity->getDescription();

            $resultado['success'] = true;
            $resultado['holiday'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarHoliday: Elimina un rol en la BD
     * @param int $holiday_id Id
     * @author Marcel
     */
    public function EliminarHoliday($holiday_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Holiday::class)
            ->find($holiday_id);
        /**@var Holiday $entity */
        if ($entity != null) {

            $holiday_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Holiday";
            $log_descripcion = "The holiday is deleted: $holiday_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarHolidays: Elimina los holidays seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarHolidays($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $holiday_id) {
                if ($holiday_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Holiday::class)
                        ->find($holiday_id);
                    /**@var Holiday $entity */
                    if ($entity != null) {

                        $holiday_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Holiday";
                        $log_descripcion = "The holiday is deleted: $holiday_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The holidays could not be deleted, because they are associated with a project";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected holidays because they are associated with a project";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarHoliday: Actuializa los datos del rol en la BD
     * @param int $holiday_id Id
     * @author Marcel
     */
    public function ActualizarHoliday($holiday_id, $day, $description)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Holiday::class)
            ->find($holiday_id);
        /** @var Holiday $entity */
        if ($entity != null) {

            //Verificar day
            $holiday = $this->getDoctrine()->getRepository(Holiday::class)
                ->BuscarHoliday($day);
            if ($holiday != null && $entity->getHolidayId() != $holiday->getHolidayId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The holiday day is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);

            if ($day != '') {
                $day = \DateTime::createFromFormat('m/d/Y', $day);
                $entity->setDay($day);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Holiday";
            $log_descripcion = "The holiday is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarHoliday: Guarda los datos de holiday en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarHoliday($day, $description)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar day
        $holiday = $this->getDoctrine()->getRepository(Holiday::class)
            ->BuscarHoliday($day);
        if ($holiday != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The holiday day is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Holiday();

        $entity->setDescription($description);

        if ($day != '') {
            $day = \DateTime::createFromFormat('m/d/Y', $day);
            $entity->setDay($day);
        }

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Holiday";
        $log_descripcion = "The holiday is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarHolidays: Listar los holidays
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarHolidays($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin)
    {
        $resultado = $this->getDoctrine()->getRepository(Holiday::class)
            ->ListarHolidaysConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $holiday_id = $value->getHolidayId();

            $data[] = array(
                "id" => $holiday_id,
                "description" => $value->getDescription(),
                "day" => $value->getDay() != '' ? $value->getDay()->format('m/d/Y') : '',
            );
        }
        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}