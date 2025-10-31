# Configuración para eliminar el error "Could not start php parser process"

El error "Could not start php parser process" ocurre porque macOS Gatekeeper está rechazando el ejecutable PHP.

## Solución (ejecutar en terminal):

```bash
sudo xattr -cr /usr/local/Cellar/php@8.2/8.2.29_1/bin/php
```

Luego reinicia Intelephense:
- Cmd+Shift+P → "Intelephense: Restart"
- Cmd+Shift+P → "Developer: Reload Window"

**Nota**: Este error no afecta la navegación ni el autocompletado. Intelephense funciona perfectamente con análisis estático sin ejecutar PHP. Puedes ignorarlo si la navegación funciona correctamente.

