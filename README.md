# Projeto Bom Samaritano

Sistema de gestão para instituições sociais e projetos assistenciais — construído para que quem cuida
de gente gaste o tempo com gente, não com papelada.

O Bom Samaritano organiza em um lugar só o que normalmente vive espalhado em cadernos, planilhas e
grupos de WhatsApp: o cadastro das crianças e de seus responsáveis, as turmas e a chamada de
frequência, a escala de professores e voluntários, o controle financeiro com doadores e prestação de
contas, os atendimentos de saúde e a comunicação com as famílias.

> «Qual destes três te parece ter sido o próximo do que caiu nas mãos dos salteadores?
> Ele respondeu: O que usou de misericórdia para com ele.
> Disse-lhe, pois, Jesus: Vai, e procede tu de igual modo.»
> — Lucas 10.36-37

---

## Contribuição livre para o Reino de Deus

Este projeto é **aberto e de contribuição livre para o Reino de Deus**. Foi escrito para servir, não
para vender.

- **Use à vontade.** Qualquer igreja, ONG, projeto social ou ministério pode baixar, instalar e usar
  este sistema, sem custo de licença e sem pedir autorização.
- **Contribua à vontade.** Correções, melhorias, tradução, documentação, relato de bugs — tudo é
  bem-vindo. Não existe contribuição pequena demais.
- **Compartilhe à vontade.** Se adaptou o sistema para a realidade da sua obra, considere devolver a
  melhoria para que outras casas também se beneficiem.

Se este código ajudar uma criança a ser bem acolhida, uma família a ser bem cuidada ou um voluntário
a servir com menos esforço, ele já cumpriu o que foi pedido dele. Toda a glória a Deus.

**Como contribuir:** abra uma issue descrevendo o que encontrou ou pretende fazer, crie um branch a
partir de `main` e envie o pull request. Antes de enviar, rode `composer ci:check` (lint, formatação,
tipos e testes). Pedimos apenas duas coisas: código em português (nomes, comentários e mensagens) e
nenhum segredo no commit.

---

## Módulos

| Módulo | O que faz |
|---|---|
| **Alunos e Responsáveis** | Cadastro completo com foto, dados socioeconômicos, endereço e autorizações |
| **Cursos e Turmas** | Turmas por curso e unidade, com capacidade, horário, dias da semana e ano letivo |
| **Matrículas** | Matrícula, trancamento e reativação, respeitando a capacidade da turma |
| **Chamada de Frequência** | Registro diário por turma, com percentual de frequência por aluno |
| **Professores e Voluntários** | Cadastro, especialidades e vínculo com unidades e turmas |
| **Expediente (Escala)** | Escala diária por unidade, justificativa de falta e ranking de assiduidade |
| **Financeiro** | Entradas e saídas por categoria, doadores, comprovantes e painel com gráficos |
| **Saúde** | Programas, áreas de atuação, convocações e atendimentos |
| **Unidades** | Operação em múltiplas unidades, com filtro por unidade em todo o sistema |
| **Notificações WhatsApp** | Envio em fila via Evolution API, com templates e variáveis (opcional) |
| **Relatórios** | PDF (ficha do aluno, frequência, lista de turma, escala, financeiro) e Excel |
| **Usuários e Permissões** | Quatro papéis com permissões granulares e registro de atividades |

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 · PHP 8.3 |
| Frontend | Vue 3 (Composition API) + Inertia.js 2 |
| Estilo | Tailwind CSS 4 · Reka UI · Lucide |
| Banco | MySQL 8 (SQLite nos testes) |
| Autenticação | Laravel Fortify (com 2FA) |
| Permissões | spatie/laravel-permission |
| Auditoria | spatie/laravel-activitylog |
| PDF / Excel | barryvdh/laravel-dompdf · maatwebsite/excel |
| Arquivos | Amazon S3 (ou disco local) |
| Testes | Pest 3 |
| Estilo de código | Laravel Pint · ESLint · Prettier |
| Deploy | Docker multi-stage (Nginx + PHP-FPM + Supervisor) |

---

## Instalação local

### Requisitos

- PHP **8.3+** com as extensões `pdo_mysql`, `mbstring`, `gd`, `zip`, `intl`, `exif` e `bcmath`
- Composer 2
- Node.js 20+ e npm
- MySQL 8 (ou MariaDB equivalente)

> **Dica:** no Windows e no macOS, o [Laravel Herd](https://herd.laravel.com) já entrega PHP, Composer
> e Nginx configurados. Basta colocar o projeto na pasta de sites do Herd e ele responde em
> `http://bomsamaritano.test`.

### Passo a passo

**1. Clone o repositório**

```bash
git clone https://github.com/brunoe2a/bomsamaritano.git
cd bomsamaritano
```

**2. Instale as dependências**

```bash
composer install
npm install
```

**3. Crie o arquivo de ambiente**

```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure o banco no `.env`**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bomsamaritano
DB_USERNAME=root
DB_PASSWORD=sua_senha_local
```

Crie o banco vazio antes de migrar:

```bash
mysql -u root -p -e "CREATE DATABASE bomsamaritano CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**5. Defina o administrador inicial no `.env`**

```dotenv
ADMIN_NAME="Administrador"
ADMIN_EMAIL=voce@suaigreja.org
ADMIN_PASSWORD=
```

Deixando `ADMIN_PASSWORD` vazio, o seeder gera uma senha forte aleatória e a exibe **uma única vez**
no terminal. Anote no gerenciador de senhas e troque no primeiro acesso.

**6. Rode as migrations e crie o acesso**

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

**7. Crie o link de storage**

```bash
php artisan storage:link
```

**8. Suba o ambiente**

```bash
composer dev
```

Esse comando sobe, em paralelo, o servidor PHP, o worker de fila e o Vite. Acesse
`http://localhost:8000` (ou o domínio do Herd) e entre com o e-mail e a senha do passo 5.

### Dados de demonstração (opcional)

Para explorar o sistema com dados fictícios — alunos, turmas, lançamentos financeiros:

```bash
php artisan db:seed
```

> **Atenção:** o `DatabaseSeeder` cria usuários de teste com senha padrão e dados falsos. Use **apenas
> em ambiente local**, nunca em produção.

---

## Comandos do dia a dia

```bash
# Ambiente completo (servidor + fila + vite)
composer dev

# Só o frontend em modo watch
npm run dev

# Build de produção
npm run build

# Testes
php artisan test
./vendor/bin/pest --filter=Matricula     # um arquivo ou caso específico

# Estilo e tipos
composer lint            # Pint corrige o PHP
npm run lint             # ESLint corrige o JS/Vue
npm run types:check      # vue-tsc

# Verificação completa antes de abrir um PR
composer ci:check

# Limpar caches após mudar .env ou rotas
php artisan optimize:clear
```

---

## Variáveis de ambiente

Todas as chaves ficam **fora do código**: no `.env` local ou nas variáveis do painel de deploy. Use o
`.env.example` como referência — ele nunca contém valores reais.

| Variável | Obrigatória | Para que serve |
|---|:---:|---|
| `APP_KEY` | sim | Chave de criptografia (`php artisan key:generate`) |
| `APP_URL` | sim | URL pública da aplicação |
| `DB_*` | sim | Conexão MySQL (host, porta, banco, usuário, senha) |
| `ADMIN_EMAIL` | sim | E-mail do administrador criado pelo `AdminSeeder` |
| `ADMIN_PASSWORD` | não | Senha do admin. Vazio = senha aleatória exibida uma vez no deploy |
| `ADMIN_NAME` | não | Nome exibido do admin (padrão: "Administrador") |
| `FILESYSTEM_DISK` | não | `s3` para guardar fotos e comprovantes na nuvem, `local` para disco |
| `AWS_*` | condicional | Credenciais e bucket S3 — obrigatórias se `FILESYSTEM_DISK=s3` |
| `MAIL_*` | não | Envio de e-mails (recuperação de senha, verificação) |
| `EVOLUTION_API_URL` / `EVOLUTION_API_KEY` | não | Notificações WhatsApp. Sem elas, o módulo fica inativo |
| `QUEUE_CONNECTION` | não | `database` em produção, para os envios em fila |

---

## Deploy

A imagem Docker é multi-stage: o primeiro estágio compila o frontend (Vite + Wayfinder) e o segundo
monta a aplicação de produção com Nginx, PHP-FPM e Supervisor num único contêiner.

### Docker

```bash
docker build -t bomsamaritano .
docker run -d -p 80:80 --env-file .env.production bomsamaritano
```

### EasyPanel (ou qualquer VPS com Docker)

1. **Crie o serviço** apontando para este repositório, com build por Dockerfile.
2. **Provisione o MySQL 8** e anote host, porta, banco, usuário e senha.
3. **Cadastre as variáveis de ambiente** no painel do serviço — nunca em arquivo versionado:

   ```dotenv
   APP_NAME="Bom Samaritano"
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=                   # gere com: php artisan key:generate --show
   APP_URL=https://seudominio.org

   DB_CONNECTION=mysql
   DB_HOST=nome_do_servico_mysql
   DB_PORT=3306
   DB_DATABASE=bomsamaritano
   DB_USERNAME=...
   DB_PASSWORD=...

   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   CACHE_STORE=database

   ADMIN_EMAIL=voce@suaigreja.org
   ADMIN_PASSWORD=            # vazio = senha aleatória, visível uma vez no log do deploy

   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=...
   AWS_SECRET_ACCESS_KEY=...
   AWS_DEFAULT_REGION=sa-east-1
   AWS_BUCKET=...
   ```

4. **Aponte o domínio** e habilite o certificado SSL (Let's Encrypt).
5. **Faça o deploy.** O `docker/entrypoint.sh` cuida sozinho de: gerar o `APP_KEY` se faltar, esperar
   o banco responder, rodar as migrations, criar o admin, cachear config/rotas/views, reiniciar a
   fila e criar o link de storage.

**Confira o log do primeiro deploy** — se `ADMIN_PASSWORD` estiver vazio, a senha gerada aparece ali
e só ali.

### Atualizações

Novo deploy significa novo build, e as migrations rodam automaticamente pelo entrypoint. Antes de uma
mudança de schema arriscada, faça backup do banco:

```bash
mysqldump -h HOST -u USUARIO -p bomsamaritano > backup.sql
```

---

## Papéis e permissões

Criados pelo `RolePermissionSeeder`, com permissões granulares por módulo (`alunos.listar`,
`financeiro.criar`, `exportar.pdf` e assim por diante).

| Papel | Alcance |
|---|---|
| `admin` | Acesso total, incluindo gestão de usuários e unidades |
| `coordenador` | Alunos, turmas, chamada, professores, voluntários e relatórios |
| `professor` | Suas turmas e o registro de chamada |
| `financeiro` | Lançamentos, categorias, doadores e relatórios financeiros |

Toda alteração relevante fica registrada no log de atividades (`spatie/laravel-activitylog`).

---

## Segurança

Este é um repositório **público** que guarda dados de **crianças e famílias em situação de
vulnerabilidade**. Algumas regras não são negociáveis:

- **Nunca commite o `.env`.** Ele está no `.gitignore` — mantenha assim. O `.env.example` existe para
  documentar as chaves, sempre com valor vazio.
- **Nunca coloque senha, token ou chave em código**, nem mesmo em seeder ou teste. Se precisar de um
  valor, leia de `config()` alimentado por `env()`.
- **Se um segredo vazar, troque o segredo.** Remover do arquivo não basta: quem já clonou o
  repositório continua com o histórico. Rotacione a credencial e só depois limpe o código.
- **Bucket S3 nunca público.** Fotos e comprovantes são servidos por URL assinada temporária.
- **Em produção, `APP_DEBUG=false`.** A página de erro do Laravel expõe variáveis de ambiente.
- **LGPD.** Colete o mínimo necessário, respeite as autorizações de imagem registradas na ficha do
  aluno e defina uma retenção para os dados de quem deixou o projeto.

Antes de subir uma mudança:

```bash
composer audit    # dependências PHP vulneráveis
npm audit         # dependências JS vulneráveis
```

Encontrou uma falha de segurança? Não abra issue pública — descreva por e-mail ao mantenedor.

---

## Testes

```bash
php artisan test
```

A suíte roda em SQLite em memória, sem tocar no banco de desenvolvimento. Ao corrigir um bug, escreva
antes o teste que o reproduz — foi assim que nasceram `TurmaMatriculaTest`,
`RelatorioFinanceiroPdfTest` e `AniversariantesTest`.

Consultas com SQL específico de um banco (`DATE_FORMAT`, `GROUP_CONCAT`) quebram no SQLite dos testes.
Prefira os helpers do Eloquent (`whereMonth`, `whereDay`, `selectRaw` com `CASE WHEN`), que funcionam
nos dois bancos.

---

## Licença

Distribuído sob a **licença MIT** — livre para usar, copiar, modificar e distribuir, inclusive por
outras obras sociais e ministérios.

*«De graça recebestes, de graça dai.»* — Mateus 10.8
