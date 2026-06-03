# Semana da Enfermagem – Município de Colares 2026

Aplicação PHP para inscrição paga da **Semana da Enfermagem – Município de Colares 2026**, com checkout via Mercado Pago.

## Funcionalidades

- Landing page institucional com informações do evento;
- Formulário de inscrição com validação server-side;
- Criação de checkout Mercado Pago para inscrição de **R$ 10,00** (pagamento único);
- Páginas de retorno do pagamento:
  - `payment_success.php`
  - `payment_pending.php`
  - `payment_failure.php`
- Persistência simples em arquivo (`data/registrations.log`) para demonstração local.

> Limitação do MVP: não há painel administrativo nem banco de dados; os registros locais são apenas para apoio ao fluxo de demonstração.

## Requisitos

- PHP 8.1+ com extensão cURL habilitada.

## Configuração

1. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

2. Edite o `.env` e informe suas credenciais do Mercado Pago:

```env
APP_URL=http://localhost:8000
MERCADOPAGO_ACCESS_TOKEN=SEU_ACCESS_TOKEN_AQUI
MERCADOPAGO_NOTIFICATION_URL=
```

### Segurança

- **Não** commite tokens reais no repositório.
- O arquivo `.env` está no `.gitignore` para proteger segredos.

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
4. O usuário é redirecionado para pagamento.
5. Após o pagamento, o Mercado Pago retorna para:
   - sucesso: `/payment_success.php`
   - pendente: `/payment_pending.php`
   - falha: `/payment_failure.php`

## Estrutura

- `index.php`: landing page + formulário de inscrição
- `process_registration.php`: validação + criação de checkout
- `payment_success.php`: retorno de pagamento aprovado
- `payment_pending.php`: retorno de pagamento pendente
- `payment_failure.php`: retorno de pagamento falho
- `config.php`: carregamento de ambiente e configurações
- `helpers.php`: utilitários e persistência local
- `assets/css/style.css`: estilos
