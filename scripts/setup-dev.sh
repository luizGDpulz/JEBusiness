#!/usr/bin/env bash
set -euo pipefail

echo "[setup-dev] Iniciando setup de desenvolvimento..."

# Tenta iniciar MariaDB/MySQL
echo "[setup-dev] tentando iniciar MariaDB/MySQL (se necessário)..."
service mariadb start 2>/dev/null || service mysql start 2>/dev/null || service mysqld start 2>/dev/null || true

# Espera até o serviço responder (se mysqladmin estiver disponível)
if command -v mysqladmin >/dev/null 2>&1; then
  echo "[setup-dev] aguardando MariaDB (até 30s)..."
  i=0
  until mysqladmin ping --silent >/dev/null 2>&1; do
    i=$((i+1))
    if [ "$i" -ge 30 ]; then
      echo "[setup-dev] timeout esperando MariaDB. Prosseguindo (verifique manualmente)."
      break
    fi
    sleep 1
  done
  echo "[setup-dev] checagem MariaDB concluída (ou timeout)."
else
  echo "[setup-dev] mysqladmin não encontrado; pulando wait."
fi

# Escolhe cliente SQL disponível
MYSQL_CMD=""
if command -v mysql >/dev/null 2>&1; then
  MYSQL_CMD="$(command -v mysql)"
elif command -v mariadb >/dev/null 2>&1; then
  MYSQL_CMD="$(command -v mariadb)"
fi

if [ -z "${MYSQL_CMD}" ]; then
  echo "[setup-dev] cliente mysql/mariadb não encontrado no PATH. Instale 'mysql-client' ou 'mariadb-client'."
  exit 1
fi

echo "[setup-dev] usando cliente: ${MYSQL_CMD}"

# Testa conexão como root (socket ou TCP)
if ! ${MYSQL_CMD} -u root -e "SELECT 1;" >/dev/null 2>&1; then
  echo "[setup-dev] não foi possível conectar como root via ${MYSQL_CMD}. Tente executar 'bash .devcontainer/init.sh <repo>' ou ver logs do MariaDB." 
else
  echo "[setup-dev] conectado como root — criando banco/usuário de desenvolvimento..."
  ${MYSQL_CMD} -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS `jebusiness` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'jebusiness'@'127.0.0.1' IDENTIFIED BY '_43690';
GRANT ALL PRIVILEGES ON jebusiness.* TO 'jebusiness'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

  echo "[setup-dev] banco e usuário criados/atualizados. Rodando migrations..."
  if command -v php >/dev/null 2>&1; then
    php scripts/migration.php || echo "[setup-dev] migrations reportaram erro (veja saída acima)."
  else
    echo "[setup-dev] php não encontrado no PATH; não foi possível rodar migrations."
  fi
fi

echo "[setup-dev] finalizado."
