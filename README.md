# Semana da Enfermagem – Município de Colares 2026

Aplicação PHP para inscrição paga da **Semana da Enfermagem – Município de Colares 2026**, com checkout via Mercado Pago, persistência SQL e área administrativa.

## Funcionalidades

- Landing page institucional com informações do evento;
- Formulário de inscrição com validação server-side;
- Criação de checkout Mercado Pago para inscrição de **R$ 10,00** (pagamento único);
- Persistência das inscrições em banco de dados MySQL;
- Atualização do status de pagamento no retorno (aprovado, pendente, falho), incluindo data de pagamento;
- Login de administrador;
- Painel administrativo com listagem completa de inscritos e dados de pagamento.

## Requisitos

- PHP 8.1+ com extensão cURL e PDO MySQL habilitadas;
- MySQL/MariaDB (XAMPP funciona normalmente).

## Configuração

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Edite o `.env` com suas credenciais:

```env
APP_URL=http://localhost:8000

MERCADOPAGO_ACCESS_TOKEN=SEU_ACCESS_TOKEN_AQUI
MERCADOPAGO_NOTIFICATION_URL=

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=semana_enfermagem
DB_USER=root
DB_PASS=

ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=COLAR_HASH_GERADO
```

## Banco de dados SQL

Importe o schema:

```bash
mysql -u root -p < database/schema.sql
```

Se preferir, execute o conteúdo de `database/schema.sql` pelo phpMyAdmin.

## Executando localmente

No diretório do projeto:

```bash
php -S localhost:8000
```

Acesse: [http://localhost:8000](http://localhost:8000)

## Fluxo de inscrição e pagamento

1. O participante preenche o formulário na home.
2. O backend valida os dados no `process_registration.php`.
3. O sistema cria uma preferência de checkout no Mercado Pago usando `MERCADOPAGO_ACCESS_TOKEN`.
4. A inscrição é salva no banco SQL.
5. O usuário é redirecionado para pagamento.
6. No retorno do Mercado Pago, o sistema atualiza o status de pagamento e a data de pagamento na tabela `registrations`.

## Área administrativa

- Login: `admin_login.php`
- Painel: `admin_dashboard.php`
- Logout: `admin_logout.php`

As credenciais de login são configuradas por `ADMIN_USERNAME` e `ADMIN_PASSWORD_HASH` no `.env`.

Gere o hash da senha com PHP (exemplo):

```bash
php -r "echo password_hash('SUA_SENHA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

## Estrutura

- `index.php`: landing page + formulário de inscrição
- `process_registration.php`: validação + checkout + gravação da inscrição
- `payment_success.php`: retorno de pagamento aprovado
- `payment_pending.php`: retorno de pagamento pendente
- `payment_failure.php`: retorno de pagamento falho
- `payment_template.php`: template de retorno e atualização de status
- `admin_login.php`: login do administrador
- `admin_dashboard.php`: listagem de inscritos
- `admin_logout.php`: encerramento da sessão admin
- `config.php`: carregamento de ambiente e configurações
- `db.php`: conexão e operações SQL
- `helpers.php`: utilitários compartilhados
- `database/schema.sql`: estrutura SQL
- `assets/css/style.css`: estilos

## Segurança

- **Nunca** commite tokens reais do Mercado Pago.
- O `.env` está no `.gitignore`.
- Defina usuário e hash de senha fortes para o administrador antes de usar em produção.
