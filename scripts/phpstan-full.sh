#!/usr/bin/env bash
# PHPStan sin baseline: lista toda la deuda conocida (útil para priorizar correcciones).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
exec vendor/bin/phpstan analyse --memory-limit=512M -c phpstan.full.neon
