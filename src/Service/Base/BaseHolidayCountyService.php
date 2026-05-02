<?php

namespace App\Service\Base;

use App\Entity\County;
use App\Entity\Holiday;
use App\Entity\Project;
use App\Entity\ProjectCounty;
use Doctrine\Persistence\ManagerRegistry;

class BaseHolidayCountyService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarTodosHolidays(): array
    {
        $holidays = [];

        /** @var \App\Repository\HolidayRepository $holidayRepo */
        $holidayRepo = $this->doctrine->getRepository(Holiday::class);
        $lista = $holidayRepo->ListarOrdenados();
        foreach ($lista as $value) {
            $holidays[] = [
                'holiday_id' => $value->getHolidayId(),
                'fecha' => $value->getDay()->format('Y-m-d'),
                'description' => $value->getDescription(),
            ];
        }

        return $holidays;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarCountysDeDistrict($district_id): array
    {
        $arreglo_resultado = [];

        /** @var \App\Repository\CountyRepository $countyRepo */
        $countyRepo = $this->doctrine->getRepository(County::class);
        $lista = $countyRepo->ListarOrdenados('', '', $district_id);
        foreach ($lista as $value) {
            $arreglo_resultado[] = [
                'county_id' => $value->getCountyId(),
                'description' => $value->getDescription(),
                'city' => $value->getCity() ?? '',
            ];
        }

        return $arreglo_resultado;
    }

    public function getCountiesDescriptionForProject(Project $project): string
    {
        if (null === $project->getProjectId()) {
            return '';
        }

        $projectCountyRepo = $this->doctrine->getRepository(ProjectCounty::class);
        $projectCounties = $projectCountyRepo->ListarCountysDeProject($project->getProjectId());
        $descriptions = [];
        foreach ($projectCounties as $projectCounty) {
            $county = $projectCounty->getCounty();
            if (null !== $county) {
                $descriptions[] = $county->getDescription();
            }
        }

        return implode(', ', $descriptions);
    }
}
