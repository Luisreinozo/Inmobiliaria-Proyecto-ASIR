#!/bin/bash

#aunque lo subo en este directorio, realemnte el script se ejecuta en el servidor BBDD que es donde estan contendidas las backups
#también es allí es donde instalo rclone

set -euo pipefail

BACKUP_DIR="/var/backups/inmobiliaria"
REMOTE="nextcloud_bbdd:BackupsBBDD"
LOGFILE="/var/log/rclone_backup_inmobiliaria.log"

NOW="$(date '+%F %T')"
echo "[$NOW] Inicio sincronizaci  n backups BBDD -> Nextcloud" >> "$LOGFILE"

#Subir al remoto las copias locales 
rclone copy "$BACKUP_DIR" "$REMOTE" \
  --log-file="$LOGFILE" \
  --log-level=INFO

#Borra los ficheros con mas de 30 dias
rclone delete "$REMOTE" \
  --min-age 30d \
  --log-file="$LOGFILE" \
  --log-level=INFO

NOW="$(date '+%F %T')"
echo "[$NOW] Fin sincronizacion backups BBDD -> Nextcloud" >> "$LOGFILE"
