## 1. Configuración de rutas y seguridad

- [x] 1.1 Decidir URL final (`/test-email` con `access_control` dedicado o mover a `/admin/...`) y actualizar `config/routes.yaml` de forma coherente con `config/packages/security.yaml`.
- [x] 1.2 Añadir regla en `access_control` que exija solo `ROLE_ADMIN` para la ruta de prueba de correo; verificar que ningún firewall deje la ruta en anónimo accidentalmente y que un usuario solo `ROLE_USER` reciba denegación.
- [x] 1.3 Si la ruta pasa a `/admin`, asegurar que el patrón del firewall `main` la cubre (`^/(admin|check|logout)`).

## 2. Lógica del controlador

- [x] 2.1 Actualizar `App\Controller\DefaultController::testemail` para obtener el usuario autenticado (`$this->getUser()` o equivalente) y usar su email como `to()` del `TemplatedEmail`.
- [x] 2.2 Manejar el caso de usuario sin email válido (respuesta 400/403 y sin llamar al mailer).
- [x] 2.3 Eliminar cualquier literal de dirección de correo destinatario del código; mantener remitente vía parámetros `mailer_sender_address` / `mailer_from_name`.
- [ ] 2.4 (Opcional según diseño) Inyectar `MailerInterface` directamente en el controlador y dejar de depender de `ScriptService::mailer` solo para este método.

## 3. Verificación

- [ ] 3.1 Probar con sesión admin: respuesta `OK` y correo recibido en el buzón del usuario conectado.
- [x] 3.2 Probar sin sesión (o con token inválido): no se envía correo y la respuesta es denegación esperada.
- [ ] 3.3 Probar con sesión de usuario autenticado que solo tenga `ROLE_USER`: no se envía correo y denegación (p. ej. 403).
- [x] 3.4 Buscar referencias a `/test-email` en documentación interna, CI o monitores y actualizarlas si la URL cambió.

## 4. OpenSpec / cierre

- [x] 4.1 Tras implementar, ejecutar `/opsx:apply` o el flujo de archivo que corresponda y marcar tareas completadas en este archivo.
