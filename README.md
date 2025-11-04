# 🧾 JEBusiness

Aplicativo web para gerenciar produtos, estoque e vendas, desenvolvido em PHP puro com MySQL e TailwindCSS.
O objetivo é oferecer uma solução leve, simples e eficiente para pequenas empresas e comércios, com controle de estoque, vendas à vista e a prazo, e relatórios gerenciais.

## 🚀 Stack Tecnológica

Componente	Tecnologia / Versão mínima

Backend	PHP: ≥ 8.1  
Banco de Dados: MySQL ≥ 8.0  
Frontend: HTML5, JS (ES6+), TailwindCSS ≥ 3  
Bibliotecas opcionais: PHPMailer, vlucas/phpdotenv, phpunit  
Servidor local: XAMPP / Laragon / PHP built-in server


## ⚙️ Funcionalidades Principais

- 🔐 Autenticação (Login/Logout) com password_hash() e sessões seguras.

- 👤 Gerenciamento de usuários e roles (Admin, Vendedor).

- 📦 CRUD de Produtos com upload de imagens e controle de estoque.

- 🔁 Movimentações de estoque (entradas e saídas automáticas).

- 💰 Vendas à vista (PIX/dinheiro) e a prazo (com contas a receber).

- 🧾 Relatórios mensais (CSV) com vendas e recebimentos.

- 📧 Integração SMTP para envio de extratos e relatórios.

- 🧱 Painel Dashboard com resumo e atalhos.

- 🧪 Testes básicos com PHPUnit.


## 🗂️ Estrutura do Projeto

```bash  

project/
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── views/
│   │   ├── login.html
│   │   ├── dashboard.html
│   │   └── products.html
│   └── .htaccess
│
├── src/
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   └── Response.php
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── ProductController.php
│   │   │   ├── SaleController.php
│   │   │   └── AuthController.php
│   │   └── Web/
│   │       └── ViewController.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Sale.php
│   │   └── User.php
│   ├── Middlewares/
│   │   ├── AuthMiddleware.php
│   │   └── CorsMiddleware.php
│   └── Helpers/
│       ├── Validation.php
│       └── Utils.php
│
├── config/
│   ├── env.php
│   └── database.php
│
├── tests/
│
└── vendor/
├── docs/                # Documentações e decisões técnicas
├── .github/             # Templates e workflows
│   ├── ISSUE_TEMPLATE.md
│   └── PULL_REQUEST_TEMPLATE.md
└── README.md
```

## 🧠 Metodologia

O projeto segue um modelo Kanban leve, com foco em fluxo contínuo.
As issues são estimadas com labels (estimate:1 a estimate:5) e movidas entre as colunas:

```
Backlog → Ready → In Progress → In Review → Done
```

## 🧩 Processo Git

- Branch principal: main (protegida).

- Branches por feature: feature/`<descricao-curta>`.

- Revisão obrigatória via Pull Request (1 reviewer mínimo).

- Merge preferido: Squash & Merge.

## ⚡ Como Rodar o Projeto Localmente
```bash
# Clonar o repositório
git clone https://github.com/seu-usuario/seu-repo.git
cd seu-repo
```
```bash
# Criar arquivo de ambiente
cp .env.example .env
```
```bash
# Configurar credenciais do banco MySQL
# e criar as tabelas
php scripts/migrate.php
php scripts/seed_admin.php
```
```bash
# Rodar servidor embutido do PHP
php -S localhost:8000 -t public
```
```bash
Acesse: http://localhost:8000
```