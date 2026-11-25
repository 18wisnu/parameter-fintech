#!/bin/bash
# Backup harian database NetBill (dijalankan cron di server, lihat deploy/netbill-backup.cron).
# Menyimpan dump terkompresi di /root/backups/daily, menyimpan 14 hari terakhir.
set -euo pipefail

APP_DIR=/root/parameter-fintech
DEST=/root/backups/daily
KEEP_DAYS=14
TS=$(date +%Y%m%d-%H%M%S)

mkdir -p "$DEST"
cd "$APP_DIR"

OUT="$DEST/netbill-$TS.sql.gz"
docker compose exec -T db sh -c 'mysqldump --single-transaction -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" 2>/dev/null' </dev/null | gzip > "$OUT.tmp"

# Dump dianggap gagal bila tidak diakhiri penanda selesai dari mysqldump
if ! zcat "$OUT.tmp" | tail -n 1 | grep -q 'Dump completed'; then
    rm -f "$OUT.tmp"
    echo "$(date '+%F %T') GAGAL: dump tidak lengkap" >> "$DEST/backup.log"
    exit 1
fi

mv "$OUT.tmp" "$OUT"
cp .env "$DEST/env-latest"
find "$DEST" -name 'netbill-*.sql.gz' -mtime +$KEEP_DAYS -delete
echo "$(date '+%F %T') OK $(du -h "$OUT" | cut -f1) $OUT" >> "$DEST/backup.log"
