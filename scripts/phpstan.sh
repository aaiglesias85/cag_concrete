#!/usr/bin/env bash
# Ejecuta PHPStan solo con rutas de phpstan.neon (ignora argumentos extra de Composer).
# Uso: composer phpstan   (no hace falta ni conviene añadir "." al final)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
exec vendor/bin/phpstan analyse --memory-limit=512M
