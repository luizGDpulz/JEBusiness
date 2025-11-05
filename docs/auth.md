# Autenticação (sessions + token)

Este documento descreve como usar e testar o sistema de autenticação básico implementado.

Endpoints:

- `GET /login` - formulário de login (HTML)
- `POST /login` - realiza login (form ou JSON). Retorna um token quando for JSON.
- `GET /logout` - encerra a sessão e redireciona para `/login`.
- `GET /dashboard` - rota protegida (exemplo)

Banco de dados / migrations:

Rodar a migration:

1. Verifique `config/env.php` (opcionalmente ajuste variáveis de ambiente)
2. Execute `php scripts/migration.php` — criará a tabela `users` (MySQL).

Seed admin:

Execute `php scripts/seed.php` — criará `admin@example.com` com senha `admin123` caso não exista.

Uso de token/API:

Ao autenticar via JSON, a resposta contém um `token` (string) — guarde-o no cliente. Para chamadas à API, envie o header:

Authorization: Bearer <token>

O token é armazenado no banco como hash (sha256) e comparado ao token fornecido.

Segurança importante:

- Senhas são hasheadas com `PASSWORD_ARGON2ID`.
- `session_regenerate_id(true)` é chamado ao autenticar.
- Cookies de sessão usam HttpOnly, Secure (se aplicável) e SameSite=Lax.
- CSRF básico implementado para formulários via token em session.

Próximos passos sugeridos (issues):

- Implementar rate-limit / lockout para endpoint de login.
- Migrar para Composer autoload e adicionar PHPStan/Psalm.
- Implementar testes ativos e CI com PHPUnit.
