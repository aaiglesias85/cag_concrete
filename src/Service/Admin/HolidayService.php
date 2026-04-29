<?php

namespace App\Service\Admin;

use App\Dto\Admin\Holiday\HolidayIdRequest;
use App\Dto\Admin\Holiday\HolidayIdsRequest;
use App\Dto\Admin\Holiday\HolidayListarRequest;
use App\Dto\Admin\Holiday\HolidaySalvarRequest;
use App\Entity\Holiday;
use App\Repository\HolidayRepository;
use App\Service\Base\Base;

class HolidayService extends Base
{
    /**
     * CargarDatosHoliday: Carga los datos de un holiday.
     *
     * @author Marcel
     */
    public function CargarDatosHoliday(HolidayIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $holiday_id = $dto->holiday_id;
        $entity = $this->getDoctrine()->getRepository(Holiday::class)
           ->find($holiday_id);
        /** @var Holiday $entity */
        if (null != $entity) {
            $arreglo_resultado['day'] = '' != $entity->getDay() ? $entity->getDay()->format('m/d/Y') : '';
            $arreglo_resultado['description'] = $entity->getDescription();

            $resultado['success'] = true;
            $resultado['holiday'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarHoliday: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarHoliday(HolidayIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $holiday_id = $dto->holiday_id;

        $entity = $this->getDoctrine()->getRepository(Holiday::class)
           ->find($holiday_id);
        /** @var Holiday $entity */
        if (null != $entity) {
            $holiday_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Holiday';
            $log_descripcion = "The holiday is deleted: $holiday_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarHolidays: Elimina los holidays seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarHolidays(HolidayIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $holiday_id) {
                if ('' != $holiday_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Holiday::class)
                       ->find($holiday_id);
                    /** @var Holiday $entity */
                    if (null != $entity) {
                        $holiday_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        ++$cant_eliminada;

                        // Salvar log
                        $log_operacion = 'Delete';
                        $log_categoria = 'Holiday';
                        $log_descripcion = "The holiday is deleted: $holiday_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The holidays could not be deleted, because they are associated with a project';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected holidays because they are associated with a project';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * Guardar Holiday (alta o edición según `holiday_id` vacío o no).
     *
     * @author Marcel
     */
    public function GuardarHoliday(HolidaySalvarRequest $d)
    {
        $holiday_id = (string) ($d->holiday_id ?? '');
        $day = (string) $d->day;
        $description = (string) $d->description;

        if ('' === $holiday_id) {
            return $this->crearHoliday($day, $description);
        }

        return $this->actualizarHoliday($holiday_id, $day, $description);
    }

    /**
     * ActualizarHoliday: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    private function actualizarHoliday(string $holiday_id, string $day, string $description)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Holiday::class)
           ->find($holiday_id);
        /** @var Holiday $entity */
        if (null != $entity) {
            // Verificar day
            /** @var HolidayRepository $holidayRepo */
            $holidayRepo = $this->getDoctrine()->getRepository(Holiday::class);
            $holiday = $holidayRepo->BuscarHoliday($day);
            if (null != $holiday && $entity->getHolidayId() != $holiday->getHolidayId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The holiday day is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);

            if ('' != $day) {
                $day = \DateTime::createFromFormat('m/d/Y', $day);
                $entity->setDay($day);
            }

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Holiday';
            $log_descripcion = "The holiday is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarHoliday: Guarda los datos de holiday en la BD.
     *
     * @author Marcel
     */
    private function crearHoliday(string $day, string $description)
    {
        $em = $this->getDoctrine()->getManager();

        // Verificar day
        /** @var HolidayRepository $holidayRepo */
        $holidayRepo = $this->getDoctrine()->getRepository(Holiday::class);
        $holiday = $holidayRepo->BuscarHoliday($day);
        if (null != $holiday) {
            $resultado['success'] = false;
            $resultado['error'] = 'The holiday day is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Holiday();

        $entity->setDescription($description);

        if ('' != $day) {
            $day = \DateTime::createFromFormat('m/d/Y', $day);
            $entity->setDay($day);
        }

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Holiday';
        $log_descripcion = "The holiday is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarHolidays: Listar los holidays.
     *
     * @author Marcel
     */
    public function ListarHolidays(HolidayListarRequest $listar)
    {
        $dt = $listar->dt;
        $fecha_inicial = $listar->fecha_inicial;
        $fecha_fin = $listar->fecha_fin;

        /** @var HolidayRepository $holidayRepo */
        $holidayRepo = $this->getDoctrine()->getRepository(Holiday::class);
        $resultado = $holidayRepo->ListarHolidaysConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $fecha_inicial,
            $fecha_fin
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $holiday_id = $value->getHolidayId();

            $data[] = [
                'id' => $holiday_id,
                'description' => $value->getDescription(),
                'day' => '' != $value->getDay() ? $value->getDay()->format('m/d/Y') : '',
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}
