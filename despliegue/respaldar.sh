#!/bin/sh
# Respaldo diario de la base y las fotos (RNF-15). Lo llama el cron del host, no un contenedor:
# mysqldump corre dentro de db y para eso hace falta docker, que un contenedor no alcanza sin su socket.
# Qué guarda y cuánto se conserva: docs/04-especificacion-tecnica/07-despliegue-y-operacion.md
set -eu
cd "$(dirname "$0")"
. ./entorno.sh

carpeta=$(leer RESPALDO_CARPETA)
dias=$(leer RESPALDO_DIAS)
base=$(leer DB_DATABASE)
clave=$(leer MYSQL_ROOT_PASSWORD)

destino="$carpeta/$(date +%F)"
# Se arma aparte y solo al final toma su fecha: si algo falla a mitad de camino, no queda una carpeta
# con fecha a medio llenar que la copia semanal pueda tomar por buena
enCurso="$carpeta/.en-curso-$$"
rm -rf "$enCurso"
mkdir -p "$enCurso"
trap 'rm -rf "$enCurso"' EXIT

# La base. --single-transaction copia un estado coherente sin detener el sistema.
# El dump no se encadena a gzip con una tubería: en sh el fallo de mysqldump se perdería y
# el respaldo quedaría a medias sin que nadie se entere.
# La entrada cerrada: «docker compose exec -T» se lleva el stdin que tenga el script, y si alguien lo
# corre a mano desde la terminal empieza a tragarse lo que teclee
docker compose exec -T -e MYSQL_PWD="$clave" db \
    mysqldump --user=root --single-transaction --no-tablespaces "$base" > "$enCurso/base.sql" < /dev/null
gzip -f "$enCurso/base.sql"

# Las fotos, del volumen taller_storage que monta web. Se crea la carpeta por si todavía no hay ninguna.
docker compose exec -T web sh -c \
    'mkdir -p /var/www/taller/sistema/storage/app/privado/fotos \
     && tar -czf - -C /var/www/taller/sistema/storage/app/privado fotos' > "$enCurso/fotos.tar.gz" < /dev/null

# La huella de cada archivo, para comprobar en la restauración que no se dañaron
(cd "$enCurso" && sha256sum base.sql.gz fotos.tar.gz > sumas.txt)

# Antes de publicarlo: que los tres archivos estén, que ninguno esté vacío y que las huellas cuadren
for archivo in base.sql.gz fotos.tar.gz sumas.txt; do
    if [ ! -s "$enCurso/$archivo" ]; then
        echo "El respaldo quedó incompleto: falta $archivo. No se publica." >&2
        exit 1
    fi
done
(cd "$enCurso" && sha256sum -c sumas.txt > /dev/null)

# Ya está completo: ahora sí toma su fecha, de un solo movimiento
rm -rf "$destino"
mv "$enCurso" "$destino"

# Los respaldos que pasan de RESPALDO_DIAS días. Solo las carpetas con fecha: nunca una que esté en curso
find "$carpeta" -mindepth 1 -maxdepth 1 -type d -name '????-??-??' -mtime "+$dias" -exec rm -rf {} +

echo "Respaldo listo en $destino ($(du -sh "$destino" | cut -f1))"
