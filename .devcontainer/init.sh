#!/usr/bin/env bash
set -euo pipefail

REPO_BASENAME="${1:-}"
if [ -z "$REPO_BASENAME" ]; then
  echo "[init] Usage: init.sh <repo-basename>"
  exit 1
fi

WORKDIR="/workspaces/${REPO_BASENAME}"
PUBLIC_DIR="${WORKDIR}/public"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"

echo "[init] repo basename: ${REPO_BASENAME}"
echo "[init] workspace: ${WORKDIR}"

# 1) Start MySQL/MariaDB (root sem senha ok para dev conforme seu pedido)
echo "[init] iniciando mysql/mariadb..."
# Tenta iniciar nomes comuns de serviço: mysql, mariadb, mysqld
service mysql start 2>/dev/null || service mariadb start 2>/dev/null || service mysqld start 2>/dev/null || true

# 2) Esperar até o mysql responder (se mysqladmin estiver disponível)
if command -v mysqladmin >/dev/null 2>&1; then
  echo "[init] aguardando mysql (até 30s)..."
  MAX=30
  i=0
  until mysqladmin ping --silent >/dev/null 2>&1; do
    i=$((i+1))
    if [ "$i" -ge "$MAX" ]; then
      echo "[init] timeout esperando mysql. Prosseguindo (verifique manualmente)."
      break
    fi
    sleep 1
  done
  echo "[init] mysql check finalizado (ou timeout)."
else
  echo "[init] mysqladmin não encontrado; pulando wait."
fi

# 3) Se existir /public no workspace, ajustar DocumentRoot do Apache PARA ESSA PASTA.
#    Se não existir, NÃO criar nada (como você pediu) — manterá /var/www/html
if [ -d "${PUBLIC_DIR}" ]; then
  echo "[init] detectado ${PUBLIC_DIR} — ajustando DocumentRoot do Apache..."
  # substitui a ocorrência padrão /var/www/html no arquivo de site
  sed -i "s#/var/www/html#${PUBLIC_DIR}#g" "${APACHE_CONF}"
else
  echo "[init] ${PUBLIC_DIR} não existe — Apache continuará com /var/www/html (não criei nada)."
fi

# 4) Ajustar permissões do workspace (silencioso se falhar)
chown -R www-data:www-data "${WORKDIR}" 2>/dev/null || true

# 5) Reiniciar/garantir Apache rodando
echo "[init] reiniciando apache..."
service apache2 restart 2>/dev/null || service httpd restart 2>/dev/null || true

echo "[init] finalizado."
echo "[init] Apache DocumentRoot atual: $(grep -Eo 'DocumentRoot .*' ${APACHE_CONF} || echo '/var/www/html')"
echo "[init] Para acessar MySQL: dentro do container rode 'mysql -u root' (dev)."
