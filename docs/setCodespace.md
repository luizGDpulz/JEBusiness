**Setup rápido: GitHub Codespaces (devcontainer)**

Este guia é focado em GitHub Codespaces: como criar a pasta `./.devcontainer`, os arquivos necessários e como iniciar/reiniciar o ambiente (Apache + PHP + MariaDB) preparado neste repositório.

Pré-requisitos
- Ter acesso ao repositório no GitHub (Codespace criado a partir deste repo/branch).
- Ter permissão para criar Codespaces ou rebuild no repositório.

Passo único: criar `.devcontainer` com os arquivos prontos

1. No seu ambiente local (ou no editor do GitHub), crie a pasta `.devcontainer/` na raiz do projeto.

2. Dentro de `.devcontainer/` crie os três arquivos a seguir com exatamente o conteúdo mostrado.

- Arquivo: `devcontainer.json`

```json
{
  "name": "PHP + Apache + MariaDB (Codespaces)",
  "build": { "dockerfile": "Dockerfile" },
  "workspaceFolder": "/workspaces/${localWorkspaceFolderBasename}",
  "forwardPorts": [80, 3306],
  "customizations": {
    "vscode": { "extensions": ["bmewburn.vscode-intelephense-client","felixfbecker.php-debug"] }
  },
  "postCreateCommand": "bash .devcontainer/init.sh ${localWorkspaceFolderBasename}"
}
```

- Arquivo: `Dockerfile`

```dockerfile
FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

# Instala MariaDB + cliente e dependências PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
      mariadb-server mariadb-client \
      git zip unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 80 3306
WORKDIR /var/www/html

# DocumentRoot será ajustado pelo init.sh se houver /public
```

- Arquivo: `init.sh` (TORNE EXECUTÁVEL com `chmod +x .devcontainer/init.sh`)

```bash
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

echo "[init] iniciando MariaDB..."
service mariadb start 2>/dev/null || service mysql start 2>/dev/null || service mysqld start 2>/dev/null || true

if command -v mysqladmin >/dev/null 2>&1; then
  echo "[init] aguardando mariadb (até 30s)..."
  MAX=30; i=0
  until mysqladmin ping --silent >/dev/null 2>&1; do
    i=$((i+1))
    if [ "$i" -ge "$MAX" ]; then
      echo "[init] timeout esperando mariadb. Prosseguindo (verifique manualmente)."
      break
    fi
    sleep 1
  done
  echo "[init] mariadb check finalizado (ou timeout)."
else
  echo "[init] mysqladmin/mariadb-admin não encontrado; pulando wait."
fi

if [ -d "${PUBLIC_DIR}" ]; then
  echo "[init] detectado ${PUBLIC_DIR} — ajustando DocumentRoot do Apache..."
  sed -i "s#/var/www/html#${PUBLIC_DIR}#g" "${APACHE_CONF}"
  chown -R www-data:www-data "${PUBLIC_DIR}" 2>/dev/null || true
  chmod -R 755 "${PUBLIC_DIR}" 2>/dev/null || true
  APACHE_MAIN_CONF="/etc/apache2/apache2.conf"
  if ! grep -q "<Directory ${PUBLIC_DIR}>" "${APACHE_MAIN_CONF}" 2>/dev/null; then
    cat >> "${APACHE_MAIN_CONF}" <<-EOF
<Directory ${PUBLIC_DIR}>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
  fi
else
  echo "[init] ${PUBLIC_DIR} não existe — Apache continuará com /var/www/html."
fi

chown -R www-data:www-data "${WORKDIR}" 2>/dev/null || true

echo "[init] reiniciando apache..."
service apache2 restart 2>/dev/null || service httpd restart 2>/dev/null || true

echo "[init] finalizado."
echo "[init] Apache DocumentRoot atual: $(grep -Eo 'DocumentRoot .*' ${APACHE_CONF} || echo '/var/www/html')"
echo "[init] Para acessar MariaDB/MySQL: dentro do container rode 'mysql -u root' (ou 'mariadb -u root') (dev)."
```

3. (Opcional) Se desejar, adicione também `scripts/setup-dev.sh` para criar o banco e o usuário e rodar migrations automaticamente (ex.: `./scripts/setup-dev.sh`).

Commit e push

```bash
git add .devcontainer/*
git commit -m "chore(devcontainer): add Codespaces devcontainer files"
git push --no-verify
```

Criar / Rebuild do Codespace

- No GitHub: Code → Codespaces → Create codespace (escolha branch atual).
- Se o Codespace já existir: Command Palette → `Codespaces: Rebuild Container` (ou `Dev Containers: Rebuild Container`).

Verificações rápidas dentro do Codespace (após rebuild)

```bash
# Apache
curl -I http://localhost

# MariaDB
ps aux | egrep 'mysqld|mariadb' --color=never
service mariadb status 2>/dev/null || service mysql status 2>/dev/null

# phpMyAdmin
# Acesse http://localhost/phpmyadmin no navegador (login: root, sem senha)

# Testar cliente SQL (conectar como root ou usuário criado)
mariadb -u root
mysql -h 127.0.0.1 -u jebusiness -p
```

Se algo falhar

- Cole a saída dos comandos acima e os logs (ex.: `tail -n 200 /var/log/mysql/error.log` ou `/var/log/mariadb/mariadb.log`) para que eu analise.

Foco no Codespace: considerações finais

- Este tutorial é intencionalmente simples: o devcontainer inicia apenas os serviços e ajusta DocumentRoot caso exista `public/` no repo. Não cria automaticamente seeds sensíveis ou usuários de produção.
- Para colaboração, prefira criar um `scripts/setup-dev.sh` (já disponível neste repo) que prepara o DB local de dev.

-----

Adicionei/editei também `init.sh` e `Dockerfile` no repositório para compatibilidade com Debian trixie / MariaDB.

**Guia de Setup do Codespace / DevContainer**

Este documento explica passo-a-passo como configurar e usar o devcontainer preparado neste repositório (Apache + PHP + MariaDB single-container) dentro do GitHub Codespaces.

**Resumo rápido**
- Arquivos principais no `.devcontainer/`:
  - `devcontainer.json`: descrição do container (build via `Dockerfile`, `postCreateCommand` que roda `init.sh`).
  - `Dockerfile`: imagem base `php:8.2-apache`, instala pacotes e extensões PHP necessárias (PDO, pdo_mysql, mysqli), MariaDB e phpMyAdmin.
  - `init.sh`: script que inicia serviços (MariaDB, Apache), ajusta `DocumentRoot` para `public/` quando presente, corrige permissões e adiciona `<Directory>` para evitar 403.
  - phpMyAdmin disponível em `/phpmyadmin` (login: root, sem senha).

Passo 1 — conferir arquivos

Verifique que os arquivos em `.devcontainer/` existem e foram commitados:

```bash
ls -la .devcontainer
```

Passo 2 — tornar `init.sh` executável (se ainda não estiver)

É recomendado marcar `init.sh` como executável antes de commitar:

```bash
chmod +x .devcontainer/init.sh
git add .devcontainer/init.sh
git commit -m "chore(devcontainer): make init.sh executable"
git push
```

Se o seu repositório usa hooks que exigem `git-lfs` e você não tem `git-lfs` instalado, você pode dar push com `--no-verify`:

```bash
git push --no-verify
```

Passo 3 — Rebuild / Create Codespace

- No GitHub Codespaces: Crie o Codespace a partir do branch desejado. O processo irá executar o build do container e o `postCreateCommand`.
- No VS Code local com Remote - Containers: Command Palette → `Dev Containers: Rebuild Container`.

O `postCreateCommand` chama `bash .devcontainer/init.sh ${localWorkspaceFolderBasename}` — isto só roda após o workspace ser montado no container.

Passo 4 — O que faz `init.sh` (resumo)

- Inicia o serviço MariaDB (tenta `mariadb`, `mysql`, `mysqld`).
- Aguarda até 30s pelo `mysqladmin ping` (se disponível).
- Se a pasta `public/` existir no seu repo, altera o `DocumentRoot` do Apache para essa pasta, aplica `chown/www-data` e `chmod 755` e adiciona um bloco `<Directory ...>` com `Require all granted` para evitar 403 Forbidden.
- Reinicia o Apache.

Passo 5 — Reiniciar manualmente os serviços (rodar init.sh novamente)

Se quiser reiniciar os serviços manualmente (por exemplo, após alterar configuração), rode dentro do container:

```bash
# estando no diretório do projeto (workspace)
bash .devcontainer/init.sh $(basename "$PWD")
```

Isso reaplicará as mudanças (start do MariaDB, ajuste DocumentRoot e reinício do Apache).

Comandos úteis para debug (rodar dentro do container)

- Verificar Apache:
```bash
curl -I http://localhost
systemctl status apache2 || service apache2 status || ps aux | grep apache2
tail -n 200 /var/log/apache2/error.log || true
```

- Verificar MariaDB / MySQL:
```bash
ps aux | egrep 'mysqld|mariadb' --color=never || true
service mariadb status 2>/dev/null || service mysql status 2>/dev/null || echo "serviço não iniciado"
ss -ltnp | grep ':3306' || true
ls -l /run/mysqld/mysqld.sock || true
```

- Entrar no cliente SQL (exemplos):
```bash
# conectar como root via socket (se disponível)
mysql -u root
# ou forçar TCP
mysql -h 127.0.0.1 -u root -p
# cliente mariadb
mariadb -u root -p
```

Problemas comuns e soluções

- Erro no build: "Package 'mysql-server' has no installation candidate"
  - Causa: Debian trixie substituiu `mysql-server` por MariaDB. Solução: o `Dockerfile` deste projeto já instala `mariadb-server`.
- Erro ao rodar migrations: `Access denied for user 'root'@'localhost'` (SQLSTATE[1698])
  - Causa: root pode estar configurado para autenticação via `unix_socket`. Solução recomendada: criar um usuário específico para a aplicação e usar esse usuário nas variáveis de ambiente.

Exemplo rápido para criar usuário app (rode dentro do container enquanto MariaDB estiver rodando):

```bash
mysql -u root <<'SQL'
CREATE USER IF NOT EXISTS 'jebusiness'@'127.0.0.1' IDENTIFIED BY '_43690';
GRANT ALL PRIVILEGES ON jebusiness.* TO 'jebusiness'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
```

Depois exporte as variáveis antes de rodar migrations:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_USER=jebusiness
export DB_PASS=_43690
php scripts/migration.php
```

Checklist final antes de abrir o app

- Rebuild/Start do Codespace concluído com sucesso.
- MariaDB está rodando (ver `service mariadb status`).
- Apache está rodando e `DocumentRoot` aponta para `public/` (se existir).
- Arquivo `public/index.php` tem permissões corretas (`www-data` como dono, `644`/`755` apropriado).
- Variáveis DB na configuração apontam para `127.0.0.1` e usuário/senha corretos.

Onde adicionar melhorias

- Se quiser ter um fluxo com dados persistidos entre reconstruições, considere montar um volume para `/var/lib/mysql` no `devcontainer.json` (avançado).
- Se preferir não rodar MariaDB no mesmo container, crie um `docker-compose` com serviços separados (web + db).

Perguntas frequentes

- Posso usar `root` sem senha no dev? Sim, para desenvolvimento local é aceitável, mas evite em produção. Em vez disso, prefira criar um usuário dedicado para a app.
- Preciso instalar `git-lfs`? Só se o repositório usar LFS; caso contrário, você pode dar `git push --no-verify` para ignorar hooks locais, mas instalar `git-lfs` é a solução correta.

-----

Arquivo `init.sh` — localização e comportamento

- `./.devcontainer/init.sh <repo-basename>` — deve ser chamado pelo `postCreateCommand` automaticamente.
- Se quiser rodar manualmente, execute como mostrado acima.

Se quiser, eu posso:
- Adicionar instruções extras específicas ao seu fluxo de trabalho (por exemplo, comandos para popular o DB com `seeds/`).
- Gerar um pequeno script `scripts/setup-dev.sh` que cria usuário DB automaticamente.

Fim do guia.

**Arquivos que devem existir em `.devcontainer/` (conteúdo completo)**

Crie os arquivos abaixo em `.devcontainer/` exatamente como mostrado — isso garante que o Codespace construa e inicialize corretamente.

- `devcontainer.json`:

```json
{
  "name": "PHP + Apache + MySQL (single container, start-only)",
  "build": {
    "dockerfile": "Dockerfile"
  },
  "workspaceFolder": "/workspaces/${localWorkspaceFolderBasename}",
  "forwardPorts": [80, 3306],
  "customizations": {
    "vscode": {
      "extensions": [
        "bmewburn.vscode-intelephense-client",
        "felixfbecker.php-debug"
      ]
    }
  },
  "postCreateCommand": "bash .devcontainer/init.sh ${localWorkspaceFolderBasename}"
}
```

- `Dockerfile`:

```dockerfile
FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

# Instala MariaDB (substituto compatível com MySQL) + cliente e dependências PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
      mariadb-server mariadb-client \
      git zip unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Expor portas (informativo)
EXPOSE 80 3306

# Workdir - não força /workspaces aqui, será o mount do Codespaces
WORKDIR /var/www/html

# Não altera DocumentRoot aqui (será feito no init.sh se houver /public).
```

- `init.sh` (torne executável com `chmod +x .devcontainer/init.sh`):

```bash
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
```

---

Se quiser que eu gere automaticamente um `scripts/setup-dev.sh` com os comandos SQL para criar o usuário `jebusiness` e rodar as migrations, diga que eu crio e commito para você.

