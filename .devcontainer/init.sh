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

# 1) Start MariaDB (root sem senha ok para dev conforme seu pedido)
echo "[init] iniciando MariaDB..."
# Tenta iniciar nomes comuns de serviço: mariadb, mysql, mysqld
service mariadb start 2>/dev/null || service mysql start 2>/dev/null || service mysqld start 2>/dev/null || true

# 2) Esperar até o mysql responder (se mysqladmin estiver disponível)
if command -v mysqladmin >/dev/null 2>&1; then
  echo "[init] aguardando mariadb (até 30s)..."
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
  echo "[init] mariadb check finalizado (ou timeout)."
else
  echo "[init] mysqladmin/mariadb-admin não encontrado; pulando wait."
fi

# 3) Se existir /public no workspace, ajustar DocumentRoot do Apache PARA ESSA PASTA.
#    Se não existir, NÃO criar nada (como você pediu) — manterá /var/www/html
if [ -d "${PUBLIC_DIR}" ]; then
  echo "[init] detectado ${PUBLIC_DIR} — ajustando DocumentRoot do Apache..."
  # substitui a ocorrência padrão /var/www/html no arquivo de site
  sed -i "s#/var/www/html#${PUBLIC_DIR}#g" "${APACHE_CONF}"
  # garantir permissões corretas na pasta pública
  chown -R www-data:www-data "${PUBLIC_DIR}" 2>/dev/null || true
  chmod -R 755 "${PUBLIC_DIR}" 2>/dev/null || true
  # garantir que exista um <Directory> com Require all granted para o public dir
  APACHE_MAIN_CONF="/etc/apache2/apache2.conf"
  if ! grep -q "<Directory ${PUBLIC_DIR}>" "${APACHE_MAIN_CONF}" 2>/dev/null; then
    echo "[init] adicionando <Directory> para ${PUBLIC_DIR} em ${APACHE_MAIN_CONF}"
    cat >> "${APACHE_MAIN_CONF}" <<-EOF
<Directory ${PUBLIC_DIR}>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
  fi
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
echo "[init] Para acessar MariaDB/MySQL: dentro do container rode 'mysql -u root' (ou 'mariadb -u root') (dev)."