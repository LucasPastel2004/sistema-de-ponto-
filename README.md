# Sistema de Ponto Online

Sistema de gestão de ponto eletrônico para empresas, construído com Laravel 11 e PHP 8.3.

## Arquitetura

O sistema adota uma arquitetura em 3 camadas (3-Tier Architecture) para manter separação de responsabilidades, testabilidade e escalabilidade:

```
Controllers / API / Filament  →  Services  →  Repositories  →  PostgreSQL
```

- **Controllers / API / Filament**: Lida com requests HTTP, validação de dados e retorno de respostas/views.
- **Services**: Contém a lógica de negócios, regras de validação complexas e fluxos operacionais.
- **Repositories**: Responsáveis pela abstração do acesso ao banco de dados e manipulação de Models.
- **Models**: Estruturas de dados base, com conversões e criptografia via Laravel Casts para conformidade com a LGPD.

## Stack Tecnológico

| Tecnologia | Função |
|------------|--------|
| PHP 8.3 | Linguagem backend |
| Laravel 11 | Framework principal |
| PostgreSQL 16 | Banco de dados |
| Redis 7 | Cache, sessões e filas |
| FilamentPHP 3 | Painel administrativo (TALL stack) |
| Filament Breezy | MFA/2FA nativo no painel Filament |
| Sanctum | Autenticação de API (SPA/Tokens) |
| Fortify | Autenticação e 2FA na API REST |
| Spatie Permission | Gestão de perfis e permissões (RBAC) |
| Pest PHP 3 | Framework de testes |
| Mockery | Mocking de dependências nos testes |
| FakerPHP | Geração de dados falsos nas factories |
| Docker & Docker Compose | Ambiente de desenvolvimento |

## Estratégia de Autenticação e MFA

O sistema separa a autenticação em dois domínios:

| Domínio | Responsável | 2FA/MFA |
|---------|-------------|---------|
| API REST (`/api/v1/*`) | **Laravel Fortify** + Sanctum | TOTP via Fortify (headless/JSON) |
| Painel Admin (`/admin`) | **Filament Breezy** | TOTP nativo do ecossistema Filament |

Essa separação evita conflitos de middleware e views entre Fortify e Filament.

## Pré-requisitos

- Docker instalado
- Docker Compose v2+

## Guia de Início Rápido

### 1. Preparação do ambiente

```bash
# Clone o repositório e entre no diretório
git clone https://github.com/LucasPastel2004/sistema-de-ponto- sistema-de-ponto
cd sistema-de-ponto

# Copie o arquivo de ambiente
cp .env.example .env
```

### 2. Build e inicialização dos containers

```bash
# Suba apenas os serviços base (sem queue-worker e scheduler por enquanto)
docker compose up -d pgsql redis app web
```

> **Nota:** O queue-worker e scheduler são iniciados separadamente após as
> migrations para evitar restart loops. O `entrypoint.sh` aguarda o banco
> automaticamente, mas é boa prática subir primeiro os serviços base.

### 3. Setup da aplicação

```bash
# Instale as dependências PHP
docker compose exec app composer install

# Publique as migrations dos pacotes (Sanctum + Spatie Permission)
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Gere a chave de encriptação (APP_KEY — usada para AES-256-CBC de PII)
docker compose exec app php artisan key:generate

# Execute as migrations
docker compose exec app php artisan migrate

# Instale o Filament
docker compose exec app php artisan filament:install --panels

# Crie o usuário administrador
docker compose exec app php artisan make:filament-user
```

> **Nota:** As migrations de `users`, `sessions` e `password_reset_tokens` já estão incluídas
> no arquivo `2024_01_01_000000_create_users_table.php` dentro do projeto.

### 4. Verifique o scaffolding com testes

```bash
# Execute a suíte de testes Pest para validar que DTOs, Services, Repositories e
# Controllers estão funcionando — espera 17 testes passando
docker compose exec app php artisan test

# Ou via Pest diretamente para output mais detalhado
docker compose exec app ./vendor/bin/pest --colors
```

### 5. Suba os workers

```bash
# Agora que as migrations estão prontas, suba os workers
docker compose up -d queue-worker scheduler

# Verifique que todos os 6 serviços estão rodando
docker compose ps
```

### 6. Acesse o sistema

| URL | Descrição |
|-----|-----------|
| `http://localhost:8000/admin` | Painel administrativo (Filament) |
| `http://localhost:8000/api/v1/pontos` | API REST (requer autenticação Sanctum) |
| `http://localhost:8000/api/user` | Endpoint de usuário autenticado (Sanctum) |
| `http://localhost:8000/docs/api` | Documentação da API (Scramble) |

## Estrutura de Diretórios Principal

```
app/
├── DTOs/                    # Data Transfer Objects (readonly classes)
├── Enums/                   # Backed string enums (TipoPonto, StatusJustificativa, etc.)
├── Filament/                # Painel Admin (Resources, Widgets, Pages)
├── Http/
│   ├── Controllers/Api/     # Controllers da API REST
│   ├── Middleware/           # ForceJsonResponse, AuditLogMiddleware
│   ├── Requests/            # Form Requests (validação)
│   └── Resources/           # API Resources (JSON transformers com wrapper data)
├── Interfaces/              # Contratos para Repository Pattern
├── Models/                  # Eloquent ORM (com encrypted casts LGPD)
├── Providers/               # Service Providers (App, Fortify, Repository, Filament)
├── Repositories/            # Implementação dos contratos (queries isoladas)
└── Services/                # Regras de negócio (RegistroPonto, CalculoJornada, etc.)
database/
├── factories/               # Model Factories (UserFactory com Hash::make)
└── migrations/              # Migrations em ordem cronológica
```

## Endpoints da API

Todas as rotas sob `/api/v1/`, protegidas por `auth:sanctum` e `throttle:api`.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/user` | Dados do usuário autenticado |
| GET | `/api/v1/pontos` | Lista histórico de pontos |
| POST | `/api/v1/pontos` | Registra novo ponto |
| GET | `/api/v1/pontos/{id}` | Detalhes de um ponto |
| GET | `/api/v1/pontos/espelho` | Espelho de ponto mensal |
| GET | `/api/v1/justificativas` | Lista justificativas |
| POST | `/api/v1/justificativas` | Cria justificativa |
| GET | `/api/v1/justificativas/{id}` | Detalhes de uma justificativa |
| PATCH | `/api/v1/justificativas/{id}/aprovar` | Aprova justificativa |
| PATCH | `/api/v1/justificativas/{id}/rejeitar` | Rejeita justificativa |
| GET | `/api/v1/colaboradores` | Lista colaboradores |
| GET | `/api/v1/colaboradores/{id}` | Detalhes de um colaborador |

## Testes

A suíte cobre **17 testes / 44 assertions** com Pest PHP 3:

```bash
# Rodar todos os testes
docker compose exec app php artisan test

# Rodar apenas testes unitários
docker compose exec app ./vendor/bin/pest --filter Unit

# Rodar apenas testes de feature
docker compose exec app ./vendor/bin/pest --filter Feature

# Testes com cobertura (requer Xdebug)
docker compose exec app ./vendor/bin/pest --coverage
```

### Cobertura dos testes

| Suite | Testes | O que cobre |
|-------|--------|-------------|
| `Unit\DTOs` | 3 | Criação e serialização de `PontoData` |
| `Unit\Enums` | 2 | Valores e labels do enum `TipoPonto` |
| `Unit\Services` | 3 | Cálculo de horas trabalhadas e intervalo (`CalculoJornadaService`) |
| `Feature\Api\AuthenticationTest` | 3 | Rejeição sem token, autenticação Sanctum, rate limiting |
| `Feature\Api\JustificativaControllerTest` | 2 | Criação e validação de justificativas |
| `Feature\Api\PontoControllerTest` | 4 | Autenticação, registro, validação e listagem de pontos |

## Observações sobre o ambiente Docker

### Git e ownership no container

O container roda com o usuário `appuser` (UID 1000), mas o volume montado do Windows pode causar conflito de ownership com o Git. Isso já está corrigido no `Dockerfile`:

```dockerfile
RUN git config --global --add safe.directory /var/www/html
```

### Composer e timeout

Em máquinas com I/O lento (como Docker Desktop no Windows), o Composer pode atingir o timeout padrão de 300s ao extrair pacotes grandes. O `composer.json` já está configurado com:

```json
"process-timeout": 0
```

Isso desativa o timeout e permite extrações demoradas sem erros.

### Hash driver: Argon2id

O projeto usa `HASH_DRIVER=argon2id` no `.env` por conformidade com OWASP. As factories e qualquer código que crie senhas deve usar `Hash::make()` e **não** `bcrypt()` diretamente, para respeitar o driver configurado.

### Roteamento da API (Laravel 11)

O arquivo `bootstrap/app.php` **não** define `apiPrefix`, pois o prefixo é controlado diretamente em `routes/api.php`:

- `GET /api/user` — rota de usuário autenticado (fora do grupo v1)
- `GET|POST /api/v1/*` — rotas da API versionada

## Segurança e Conformidade LGPD

| Controle | Implementação |
|----------|---------------|
| **Criptografia de PII** | Campos sensíveis (CPF, CNPJ) via cast `encrypted` (AES-256-CBC) |
| **Senhas** | Argon2id como driver padrão (OWASP) — `HASH_DRIVER=argon2id` |
| **API Auth** | Sanctum tokens + Fortify 2FA |
| **Painel Auth** | Filament Breezy com TOTP |
| **Rate Limiting** | 60 req/min API, 5 req/min login |
| **XSS** | CSP headers (Nginx), sanitização Blade/Livewire |
| **CSRF** | Tokens anti-CSRF via middleware |
| **SQLi** | Prepared Statements via Eloquent ORM |
| **Audit Trail** | AuditLog imutável + AuditLogMiddleware |
| **Headers** | HSTS, X-Frame-Options, X-Content-Type-Options |
| **PHP** | `expose_php=Off`, `allow_url_fopen=Off`, sessions seguras |

## Contribuição

Siga os padrões PSR-12 e utilize tipagem estrita (`declare(strict_types=1)`) em todos os novos arquivos PHP.

## Licença

Este projeto é de código aberto sob a licença [MIT](https://opensource.org/licenses/MIT).
