<?php

namespace App\Service\Base;

use App\Entity\InvoiceItem;
use App\Entity\Project;
use App\Entity\Usuario;
use App\Service\Admin\UserPermissionMenuService;
use App\Service\Admin\WidgetAccessService;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

class Base
{
    private ?BaseCleanupService $baseCleanupService = null;
    private ?BaseDateFormatService $baseDateFormatService = null;
    private ?BaseFileLogService $baseFileLogService = null;
    private ?BaseInvoicePaymentsDisplayService $baseInvoicePaymentsDisplayService = null;
    private ?BaseApplicationLogService $baseApplicationLogService = null;
    private ?BaseCalendarMonthService $baseCalendarMonthService = null;
    private ?BaseConcreteYieldMetricsService $baseConcreteYieldMetricsService = null;
    private ?BaseContactListingService $baseContactListingService = null;
    private ?BaseHolidayCountyService $baseHolidayCountyService = null;
    private ?BaseItemYieldCatalogService $baseItemYieldCatalogService = null;
    private ?BasePasswordService $basePasswordService = null;
    private ?BaseProjectNotesWriterService $baseProjectNotesWriterService = null;
    private ?BaseTextNormalizationService $baseTextNormalizationService = null;
    private ?UserPermissionMenuService $userPermissionMenuService = null;

    public function __construct(
        protected ManagerRegistry $doctrine,
        public MailerInterface $mailer,
        protected ContainerBagInterface $containerBag,
        public Security $security,
        public LoggerInterface $logger,
        protected UrlGeneratorInterface $urlGenerator,
        protected Environment $twig,
        protected WidgetAccessService $widgetAccessService,
    ) {
    }

    #[Required]
    public function setBaseFileLogService(BaseFileLogService $baseFileLogService): void
    {
        $this->baseFileLogService = $baseFileLogService;
    }

    #[Required]
    public function setBaseDateFormatService(BaseDateFormatService $baseDateFormatService): void
    {
        $this->baseDateFormatService = $baseDateFormatService;
    }

    #[Required]
    public function setBaseCleanupService(BaseCleanupService $baseCleanupService): void
    {
        $this->baseCleanupService = $baseCleanupService;
    }

    #[Required]
    public function setBaseInvoicePaymentsDisplayService(BaseInvoicePaymentsDisplayService $baseInvoicePaymentsDisplayService): void
    {
        $this->baseInvoicePaymentsDisplayService = $baseInvoicePaymentsDisplayService;
    }

    #[Required]
    public function setBaseApplicationLogService(BaseApplicationLogService $baseApplicationLogService): void
    {
        $this->baseApplicationLogService = $baseApplicationLogService;
    }

    #[Required]
    public function setBaseCalendarMonthService(BaseCalendarMonthService $baseCalendarMonthService): void
    {
        $this->baseCalendarMonthService = $baseCalendarMonthService;
    }

    #[Required]
    public function setBaseConcreteYieldMetricsService(BaseConcreteYieldMetricsService $baseConcreteYieldMetricsService): void
    {
        $this->baseConcreteYieldMetricsService = $baseConcreteYieldMetricsService;
    }

    #[Required]
    public function setBaseContactListingService(BaseContactListingService $baseContactListingService): void
    {
        $this->baseContactListingService = $baseContactListingService;
    }

    #[Required]
    public function setBaseHolidayCountyService(BaseHolidayCountyService $baseHolidayCountyService): void
    {
        $this->baseHolidayCountyService = $baseHolidayCountyService;
    }

    #[Required]
    public function setBaseItemYieldCatalogService(BaseItemYieldCatalogService $baseItemYieldCatalogService): void
    {
        $this->baseItemYieldCatalogService = $baseItemYieldCatalogService;
    }

    #[Required]
    public function setBasePasswordService(BasePasswordService $basePasswordService): void
    {
        $this->basePasswordService = $basePasswordService;
    }

    #[Required]
    public function setBaseProjectNotesWriterService(BaseProjectNotesWriterService $baseProjectNotesWriterService): void
    {
        $this->baseProjectNotesWriterService = $baseProjectNotesWriterService;
    }

    #[Required]
    public function setBaseTextNormalizationService(BaseTextNormalizationService $baseTextNormalizationService): void
    {
        $this->baseTextNormalizationService = $baseTextNormalizationService;
    }

    #[Required]
    public function setUserPermissionMenuService(UserPermissionMenuService $userPermissionMenuService): void
    {
        $this->userPermissionMenuService = $userPermissionMenuService;
    }

    // doctrine manager
    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    /**
     * Summary of getUser.
     *
     * @return UserInterface|Usuario|null
     */
    public function getUser()
    {
        return $this->security->getUser();
    }

    // Generates a URL from the given parameters.
    public function generateUrl(string $route, array $parameters = [], int $referenceType = 1): string
    {
        return $this->urlGenerator->generate($route, $parameters, $referenceType);
    }

    // Returns a rendered view.
    public function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    // getParameter
    public function getParameter(string $name)
    {
        return $this->containerBag->get($name);
    }

    // Is mobile
    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
    }

    // escribir log
    public function writelog($txt, $filename = 'weblog.txt')
    {
        $this->getBaseFileLogService()->writelog($txt, $filename);
    }

    /**
     * Escribe siempre en public/{filename} (ruta absoluta vía kernel.project_dir).
     * Útil cuando el CWD del PHP no es public/ y weblog.txt quedaría en otro sitio.
     */
    public function writelogPublic(string $txt, string $filename = 'weblog.txt'): void
    {
        $this->getBaseFileLogService()->writelogPublic($txt, $filename);
    }

    /**
     * Mismo año-mes calendario (Y-m), para reglas de override por período.
     */
    protected function isSameCalendarMonth(\DateTimeInterface $invoiceStart, \DateTimeInterface $overridePeriodDate): bool
    {
        return $this->getBaseCalendarMonthService()->isSameCalendarMonth($invoiceStart, $overridePeriodDate);
    }

    // http://www.the-art-of-web.com/php/truncate/
    public function truncate($string, $limit, $break = '.', $pad = '...')
    {
        return $this->getBaseDateFormatService()->truncate($string, $limit, $break, $pad);
    }

    // hacer url
    public function HacerUrl($tex)
    {
        return $this->getBaseFileLogService()->HacerUrl($tex);
    }

    // Obterner bien la url
    public function ObtenerURL()
    {
        $s = '';
        if (!empty($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) {
            $s = 's';
        }

        $protocol = $this->strleft(strtolower($_SERVER['SERVER_PROTOCOL']), ' / ').$s;

        $ruta = $this->generateUrl('home');
        if (substr_count($ruta, 'index.php') > 0) {
            $ruta = $this->generateUrl('home').'../../';
        } else {
            $ruta = $this->generateUrl('home').'../';
        }

        $direccion_url = 'http'.$s.'://'.$_SERVER['HTTP_HOST'].$ruta;

        return $direccion_url;
    }

    private function strleft($s1, $s2)
    {
        return substr($s1, 0, strpos($s1, $s2));
    }

    /* Devuelve el ip */
    public function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip;
    }

    // Cambiar time zone
    public function setTimeZone($zone = 'America/Santiago')
    {
        $this->getBaseDateFormatService()->setTimeZone($zone);
    }

    // Devolver fecha actual
    public function ObtenerFechaActual($format = 'Y-m-d')
    {
        return $this->getBaseDateFormatService()->ObtenerFechaActual($format);
    }

    // primer dia del mes
    public function ObtenerPrimerDiaMes()
    {
        return $this->getBaseDateFormatService()->ObtenerPrimerDiaMes();
    }

    // Devuelve la fecha del mensaje en el formato Agosto, 25 H:i
    public function DevolverFechaFormatoBarras($fecha)
    {
        return $this->getBaseDateFormatService()->DevolverFechaFormatoBarras($fecha);
    }

    // get size
    public function getSize($archivo)
    {
        return $this->getBaseFileLogService()->getSize($archivo);
    }

    /**
     * ListarPermisosDeUsuario: Carga todos los permisos de un perfil.
     *
     * @param int $usuario_id Id
     *
     * @author Marcel
     */
    public function ListarPermisosDeUsuario($usuario_id)
    {
        return $this->getUserPermissionMenuService()->ListarPermisosDeUsuario($usuario_id);
    }

    // Devolver menu de ican
    public function DevolverMenu($usuario_id)
    {
        return $this->getUserPermissionMenuService()->DevolverMenu($usuario_id);
    }

    // Devolver primera funcion con permiso
    public function DevolverPrimeraFuncionDeUsuario($usuario_id)
    {
        return $this->getUserPermissionMenuService()->DevolverPrimeraFuncionDeUsuario($usuario_id);
    }

    /**
     * BuscarPermiso: Busca un permiso determinado.
     *
     * @param int $usuario_id Id
     * @param int $funcion_id Id
     *
     * @author Marcel
     */
    public function BuscarPermiso($usuario_id, $funcion_id)
    {
        return $this->getUserPermissionMenuService()->BuscarPermiso($usuario_id, $funcion_id);
    }

    private function getUserPermissionMenuService(): UserPermissionMenuService
    {
        if (null === $this->userPermissionMenuService) {
            // Fallback defensivo para instancias creadas manualmente fuera del contenedor.
            $this->userPermissionMenuService = new UserPermissionMenuService($this->doctrine, $this->widgetAccessService);
        }

        return $this->userPermissionMenuService;
    }

    // subir un archivo
    // src/Service/Base/Base.php

    public function upload(UploadedFile $file, $dir, $aceptedExtensions = [])
    {
        return $this->getBaseFileLogService()->upload($file, $dir, $aceptedExtensions);
    }

    private function getBaseFileLogService(): BaseFileLogService
    {
        if (null === $this->baseFileLogService) {
            // Fallback defensivo para instancias creadas manualmente fuera del contenedor.
            $this->baseFileLogService = new BaseFileLogService($this->logger, $this->containerBag);
        }

        return $this->baseFileLogService;
    }

    private function getBaseDateFormatService(): BaseDateFormatService
    {
        if (null === $this->baseDateFormatService) {
            // Fallback defensivo para instancias creadas manualmente fuera del contenedor.
            $this->baseDateFormatService = new BaseDateFormatService();
        }

        return $this->baseDateFormatService;
    }

    private function getBaseCleanupService(): BaseCleanupService
    {
        if (null === $this->baseCleanupService) {
            // Fallback defensivo para instancias creadas manualmente fuera del contenedor.
            $this->baseCleanupService = new BaseCleanupService($this->doctrine);
        }

        return $this->baseCleanupService;
    }

    private function getBaseInvoicePaymentsDisplayService(): BaseInvoicePaymentsDisplayService
    {
        if (null === $this->baseInvoicePaymentsDisplayService) {
            $this->baseInvoicePaymentsDisplayService = new BaseInvoicePaymentsDisplayService($this->doctrine);
        }

        return $this->baseInvoicePaymentsDisplayService;
    }

    private function getBaseApplicationLogService(): BaseApplicationLogService
    {
        if (null === $this->baseApplicationLogService) {
            $this->baseApplicationLogService = new BaseApplicationLogService(
                $this->doctrine,
                $this->security,
                $this->getBaseTextNormalizationService(),
            );
        }

        return $this->baseApplicationLogService;
    }

    private function getBaseCalendarMonthService(): BaseCalendarMonthService
    {
        if (null === $this->baseCalendarMonthService) {
            $this->baseCalendarMonthService = new BaseCalendarMonthService();
        }

        return $this->baseCalendarMonthService;
    }

    private function getBaseConcreteYieldMetricsService(): BaseConcreteYieldMetricsService
    {
        if (null === $this->baseConcreteYieldMetricsService) {
            $this->baseConcreteYieldMetricsService = new BaseConcreteYieldMetricsService(
                $this->doctrine,
                new BaseYieldExpressionService(),
            );
        }

        return $this->baseConcreteYieldMetricsService;
    }

    private function getBaseContactListingService(): BaseContactListingService
    {
        if (null === $this->baseContactListingService) {
            $this->baseContactListingService = new BaseContactListingService($this->doctrine);
        }

        return $this->baseContactListingService;
    }

    private function getBaseHolidayCountyService(): BaseHolidayCountyService
    {
        if (null === $this->baseHolidayCountyService) {
            $this->baseHolidayCountyService = new BaseHolidayCountyService($this->doctrine);
        }

        return $this->baseHolidayCountyService;
    }

    private function getBaseItemYieldCatalogService(): BaseItemYieldCatalogService
    {
        if (null === $this->baseItemYieldCatalogService) {
            $this->baseItemYieldCatalogService = new BaseItemYieldCatalogService(
                $this->doctrine,
                $this->getBaseDateFormatService(),
            );
        }

        return $this->baseItemYieldCatalogService;
    }

    private function getBasePasswordService(): BasePasswordService
    {
        if (null === $this->basePasswordService) {
            $this->basePasswordService = new BasePasswordService();
        }

        return $this->basePasswordService;
    }

    private function getBaseProjectNotesWriterService(): BaseProjectNotesWriterService
    {
        if (null === $this->baseProjectNotesWriterService) {
            $this->baseProjectNotesWriterService = new BaseProjectNotesWriterService($this->doctrine);
        }

        return $this->baseProjectNotesWriterService;
    }

    private function getBaseTextNormalizationService(): BaseTextNormalizationService
    {
        if (null === $this->baseTextNormalizationService) {
            $this->baseTextNormalizationService = new BaseTextNormalizationService();
        }

        return $this->baseTextNormalizationService;
    }

    /**
     * EliminarInformacionDeUsuario.
     *
     * @return void
     */
    public function EliminarInformacionDeUsuario($usuario_id)
    {
        $this->getBaseCleanupService()->EliminarInformacionDeUsuario($usuario_id);
    }

    // codificar contraseña
    public function CodificarPassword($pass)
    {
        return $this->getBasePasswordService()->CodificarPassword($pass);
    }

    // verificar contraseña
    public function VerificarPassword($password, $hash)
    {
        return $this->getBasePasswordService()->VerificarPassword($password, $hash);
    }

    /**
     * Normaliza texto para guardar en BD (evita caracteres Unicode que fallan en charset latin1/utf8).
     * Reemplaza espacio estrecho (U+202F), no-break space (U+00A0), BOM (U+FEFF), etc. por espacio normal.
     */
    protected function normalizarTextoParaDb(?string $text): string
    {
        return $this->getBaseTextNormalizationService()->normalizarTextoParaDb($text);
    }

    /**
     * SalvarLog.
     *
     * @return void
     */
    public function SalvarLog($operacion, $categoria, $descripcion)
    {
        $this->getBaseApplicationLogService()->SalvarLog($operacion, $categoria, $descripcion);
    }

    // ordenar array php
    public function ordenarArrayAsc($array, $prop)
    {
        $lista = $array;

        for ($i = 1; $i < count($lista); ++$i) {
            for ($j = 0; $j < count($lista) - $i; ++$j) {
                if ($lista[$j]["$prop"] > $lista[$j + 1]["$prop"]) {
                    $k = $lista[$j + 1];
                    $lista[$j + 1] = $lista[$j];
                    $lista[$j] = $k;
                }
            }
        }

        return $lista;
    }

    public function ordenarArrayDesc($array, $prop)
    {
        $lista = $array;

        for ($i = 1; $i < count($lista); ++$i) {
            for ($j = 0; $j < count($lista) - $i; ++$j) {
                if ($lista[$j]["$prop"] < $lista[$j + 1]["$prop"]) {
                    $k = $lista[$j + 1];
                    $lista[$j + 1] = $lista[$j];
                    $lista[$j] = $k;
                }
            }
        }

        return $lista;
    }

    /**
     * ListarYieldsCalculation.
     *
     * @return array
     */
    public function ListarYieldsCalculation()
    {
        return $this->getBaseItemYieldCatalogService()->ListarYieldsCalculation();
    }

    /**
     * BuscarYieldCalculation.
     *
     * @return string
     */
    public function BuscarYieldCalculation($id)
    {
        return $this->getBaseItemYieldCatalogService()->BuscarYieldCalculation($id);
    }

    /**
     * DevolverYieldCalculationDeItemProject.
     *
     * @param \App\Entity\ProjectItem|\App\Entity\EstimateQuoteItem $item_entity
     *
     * @return string
     */
    public function DevolverYieldCalculationDeItemProject($item_entity)
    {
        return $this->getBaseItemYieldCatalogService()->DevolverYieldCalculationDeItemProject($item_entity);
    }

    /**
     * ListarUltimaNotaDeProject.
     *
     * @return array|null
     */
    public function ListarUltimaNotaDeProject($project_id)
    {
        return $this->getBaseItemYieldCatalogService()->ListarUltimaNotaDeProject($project_id);
    }

    /**
     * AgregarNewItem.
     *
     * @return \App\Entity\Item
     */
    public function AgregarNewItem($value, $equation_entity)
    {
        return $this->getBaseItemYieldCatalogService()->AgregarNewItem($value, $equation_entity);
    }

    /***
     * ListarTodosItems
     * @return array
     */
    public function ListarTodosItems()
    {
        return $this->getBaseItemYieldCatalogService()->ListarTodosItems();
    }

    /**
     * DevolverItem.
     *
     * @param \App\Entity\Item $value
     *
     * @return array
     */
    public function DevolverItem($value)
    {
        return $this->getBaseItemYieldCatalogService()->DevolverItem($value);
    }

    /**
     * DevolverYieldCalculationDeItem.
     *
     * @param \App\Entity\Item $item_entity
     *
     * @return string
     */
    public function DevolverYieldCalculationDeItem($item_entity)
    {
        return $this->getBaseItemYieldCatalogService()->DevolverYieldCalculationDeItem($item_entity);
    }

    /**
     * Normaliza string opcional (trim; vacío → null).
     */
    protected function normalizeNullableTrimmedString($value): ?string
    {
        return $this->getBaseTextNormalizationService()->normalizeNullableTrimmedString($value);
    }

    /**
     * EliminarInformacionRelacionadaDataTracking.
     *
     * @return void
     */
    public function EliminarInformacionRelacionadaDataTracking($data_tracking_id)
    {
        $this->getBaseCleanupService()->EliminarInformacionRelacionadaDataTracking($data_tracking_id);
    }

    /**
     * ListarContactsDeProject
     * Compatible with project_contact.company_contact_id NULL (legacy records).
     *
     * @return array
     */
    public function ListarContactsDeProject($project_id)
    {
        return $this->getBaseContactListingService()->ListarContactsDeProject($project_id);
    }

    /**
     * ListarContactsDeConcreteVendor.
     *
     * @return array
     */
    public function ListarContactsDeConcreteVendor($vendor_id)
    {
        return $this->getBaseContactListingService()->ListarContactsDeConcreteVendor($vendor_id);
    }

    /***
     * ObtenerSemanasReporteExcel
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return array
     */
    public function ObtenerSemanasReporteExcel(?string $fechaInicio, ?string $fechaFin, string $formato = 'm/d/Y'): array
    {
        return $this->getBaseDateFormatService()->ObtenerSemanasReporteExcel($fechaInicio, $fechaFin, $formato);
    }

    public function estilizarCelda($sheet, $coord, $styleArray, $bold = false, $align = Alignment::HORIZONTAL_CENTER, $wrapText = false)
    {
        $sheet->getStyle($coord)->applyFromArray($styleArray);
        $sheet->getStyle($coord)->getAlignment()->setHorizontal($align);
        $sheet->getStyle($coord)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        if ($wrapText) {
            $sheet->getStyle($coord)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension((int) filter_var($coord, FILTER_SANITIZE_NUMBER_INT))->setRowHeight(-1); // auto height
        }

        if ($bold) {
            $sheet->getStyle($coord)->getFont()->setBold(true);
        }
    }

    /**
     * ListarContactsDeCompany.
     *
     * @return array
     */
    public function ListarContactsDeCompany($company_id)
    {
        return $this->getBaseContactListingService()->ListarContactsDeCompany($company_id);
    }

    /**
     * CalcularTotalConcreteYiel.
     *
     * @return float
     */
    public function CalcularTotalConcreteYiel($data_tracking_id)
    {
        return $this->getBaseConcreteYieldMetricsService()->CalcularTotalConcreteYiel($data_tracking_id);
    }

    /**
     * CalcularTotalConcreteYielItem.
     *
     * @param \App\Entity\DataTrackingItem $data_tracking_item
     *
     * @return float
     */
    public function CalcularTotalConcreteYielItem($data_tracking_item)
    {
        return $this->getBaseConcreteYieldMetricsService()->CalcularTotalConcreteYielItem($data_tracking_item);
    }

    /**
     * CalcularLostConcrete.
     *
     * @param \App\Entity\DataTracking $value
     *
     * @return float
     */
    public function CalcularLostConcrete($value)
    {
        return $this->getBaseConcreteYieldMetricsService()->CalcularLostConcrete($value);
    }

    /**
     * ListarTodosHolidays.
     *
     * @return array
     */
    public function ListarTodosHolidays()
    {
        return $this->getBaseHolidayCountyService()->ListarTodosHolidays();
    }

    /**
     * ListarCountysDeDistrict.
     *
     * @return array
     */
    public function ListarCountysDeDistrict($district_id)
    {
        return $this->getBaseHolidayCountyService()->ListarCountysDeDistrict($district_id);
    }

    /**
     * getCountiesDescriptionForProject: Obtiene la descripción de los counties de un project desde la tabla intermedia.
     *
     * @return string
     */
    public function getCountiesDescriptionForProject(Project $project)
    {
        return $this->getBaseHolidayCountyService()->getCountiesDescriptionForProject($project);
    }

    /**
     * SalvarNotesUpdate.
     *
     * @param Project $entity
     *
     * @return void
     */
    public function SalvarNotesUpdate($entity, $notas)
    {
        $this->getBaseProjectNotesWriterService()->SalvarNotesUpdate($entity, $notas);
    }

    /**
     * EliminarInformacionDeInvoice.
     *
     * @return void
     */
    public function EliminarInformacionDeInvoice($invoice_id)
    {
        $this->getBaseCleanupService()->EliminarInformacionDeInvoice($invoice_id);
    }

    /**
     * Mismo unpaid_qty que muestra la grilla de Payments (no el campo persistido invoice_item.unpaid_qty).
     * quantity_final − paid_qty; bond usa bon_quantity del invoice; notas con override_unpaid_qty (orden DESC).
     *
     * @param array<int, array<string, mixed>>|null $notes si null, se cargan con ListarNotesDeItemInvoice
     */
    public function computeUnpaidQtyForPaymentsDisplay(InvoiceItem $value, ?array $notes = null): float
    {
        return $this->getBaseInvoicePaymentsDisplayService()->computeUnpaidQtyForPaymentsDisplay($value, $notes);
    }

    /**
     * ListarPaymentsDeInvoice.
     *
     * @return array
     */
    public function ListarPaymentsDeInvoice($invoice_id)
    {
        return $this->getBaseInvoicePaymentsDisplayService()->ListarPaymentsDeInvoice($invoice_id);
    }

    /**
     * ListarNotesDeItemInvoice.
     *
     * @return array
     */
    public function ListarNotesDeItemInvoice($invoice_item_id)
    {
        return $this->getBaseInvoicePaymentsDisplayService()->ListarNotesDeItemInvoice($invoice_item_id);
    }
}
