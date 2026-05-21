## 1. Base de datos

- [x] 1.1 Crear `database/2026_05_20_project_city_id.sql` con `ALTER TABLE project ADD COLUMN city_id INT NULL`, FK a `county(county_id)` e índice
- [x] 1.2 Ejecutar el script en el entorno de desarrollo y verificar integridad

## 2. Capa de dominio y persistencia

- [x] 2.1 Añadir relación `ManyToOne` `city` / columna `city_id` en `src/Entity/Project.php`
- [x] 2.2 Implementar `CountyRepository::ListarCiudadesOrdenadas()` (filtro `city` no vacío, status activo si aplica)
- [x] 2.3 Ajustar `CountyRepository::ListarOrdenados()` usado en proyecto para excluir registros tipo City del select de condados (o filtrar en controlador)
- [x] 2.4 Ampliar `ProjectService`: parsear `city_id` en requests, guardar en alta/actualización, devolver `city_id` y etiqueta en `cargarDatos`
- [x] 2.5 Registrar cambio de ciudad en historial/notas del proyecto si el flujo de counties ya audita cambios similares
- [x] 2.6 Ampliar `ProjectActualizarRequest` (y DTO de salvar si existe) con `city_id` opcional
- [x] 2.7 Ampliar `SePuedeEliminarCounty` (o validación relacionada) para bloquear borrado de county referenciado por `project.city_id`

## 3. Admin — controlador y plantilla

- [x] 3.1 Pasar variable `cities` desde `ProjectController::index` al Twig
- [x] 3.2 En `templates/admin/project/index.html.twig`, añadir `#select-city` con `<select id="city">` al lado de County; ajustar columnas de la fila
- [x] 3.3 En vista detalle del mismo template, añadir `#city-detalle` junto a `#county-detalle`

## 4. JavaScript admin

- [x] 4.1 En `projects.js`: enviar `city_id` al guardar; al cargar proyecto, setear `#city`; reset en formulario nuevo
- [x] 4.2 En `projects-detalle.js`: mostrar descripción de ciudad en `#city-detalle` desde payload de carga

## 5. API y pruebas manuales

- [x] 5.1 Si `App\Controller\App\ProjectController::cargarDatos` reutiliza serialización admin, incluir `city_id` y texto de ciudad en JSON
- [ ] 5.2 Probar: crear proyecto con county + city; editar y quitar city; detalle muestra ciudad; county select no lista ciudades; eliminar county con proyecto vinculado por `city_id` bloqueado
