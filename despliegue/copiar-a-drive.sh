#!/bin/sh
# Copia semanal del respaldo más reciente a Google Drive (RNF-15). La llama el cron del host los domingos.
# La cuenta de Google se autoriza una sola vez en el servidor con «rclone config».
set -eu
cd "$(dirname "$0")"
. ./entorno.sh

carpeta=$(leer RESPALDO_CARPETA)
remoto=$(leer RESPALDO_REMOTO)
copias=$(leer RESPALDO_COPIAS_REMOTAS)

# El más reciente por nombre: las carpetas se llaman AAAA-MM-DD, así que el orden alfabético es el cronológico.
# Se busca el más reciente que esté completo y cuyas huellas cuadren: subir uno dañado y después borrar
# el anterior por retención dejaría a Drive sin ninguna copia buena
ultimo=""
for candidato in $(find "$carpeta" -mindepth 1 -maxdepth 1 -type d -name '????-??-??' | sort -r); do
    if [ -s "$candidato/base.sql.gz" ] && [ -s "$candidato/fotos.tar.gz" ] && [ -s "$candidato/sumas.txt" ]        && (cd "$candidato" && sha256sum -c sumas.txt > /dev/null 2>&1); then
        ultimo="$candidato"
        break
    fi
    echo "Se omite $(basename "$candidato"): está incompleto o sus huellas no cuadran" >&2
done

if [ -z "$ultimo" ]; then
    echo "No hay ningún respaldo completo en $carpeta: ¿corrió respaldar.sh?" >&2
    exit 1
fi

fecha=$(basename "$ultimo")
rclone copy "$ultimo" "$remoto/$fecha"

# Que lo que quedó en Drive sea igual a lo que se subió. Si no, se corta aquí y no se borra ninguna copia vieja
rclone check "$ultimo" "$remoto/$fecha" --one-way

# Las copias que pasan de RESPALDO_COPIAS_REMOTAS, de la más vieja a la más nueva.
# Son unos 2,6 GB cada una después de 3 años y Google Drive regala 15 GB (RNF-15).
sobran=$(rclone lsf --dirs-only "$remoto" | sed 's|/$||' | sort | head -n "-$copias")

for vieja in $sobran; do
    rclone purge "$remoto/$vieja"
    echo "Se borró de Google Drive la copia $vieja"
done

echo "Copia de $fecha en $remoto"
