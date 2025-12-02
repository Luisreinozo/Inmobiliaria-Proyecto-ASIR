#!/bin/bash
set -euo pipefail

DB_NAME="inmobiliaria"
DB_USER="backup"
#la conttraseña va en texto plano porque el archivo lo dejo con permisos solo de ejecutar por el propietario para no complicarlo más
DB_PASS="Backup1234."
BACKUP_DIR="/var/backups/inmobiliaria"
DATE="$(date +%F_%H%M%S)"

mkdir -p "$BACKUP_DIR"

mysqldump 
  -u"$DB_USER" -p"$DB_PASS" \
  --single-transaction \
  # es para las procedimientos almacenados, funciones y tareas automatizadas de MySQL que no tengo en la BD pero si luego se incorporan ya está contemplado
  --routines --events \
  "$DB_NAME" \
  | gzip > "$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"

# Rotación de los backups, borrando ficheros con más de 30 días
find "$BACKUP_DIR" -type f -name "${DB_NAME}_*.sql.gz" -mtime +30 -delete
