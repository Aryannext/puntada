#!/bin/sh
# Despliega lo último de main en el VPS: sh despliegue/desplegar.sh
# Solo se despliega un commit que pasa las comprobaciones: hoy las corre GitHub Actions en cada envío, y scripts/calidad.py las repite en la máquina de desarrollo.
set -eu
cd "$(dirname "$0")/.."

git pull --ff-only origin main
# exec carga aplicar.sh ya actualizado por el pull
exec sh despliegue/aplicar.sh
