# MY FIGHT co

Sistema web desenvolvido para controle de estoque e acompanhamento da produção de quimonos em uma pequena confecção.

O projeto faz parte do Trabalho de Conclusão de Curso de Ciência da Computação da Universidade Paulista (UNIP).

## Objetivo

Centralizar o controle de materiais, produtos acabados, movimentações de estoque e ordens de produção, substituindo controles manuais e facilitando o acompanhamento do processo produtivo.

## Principais funcionalidades

O sistema possui:

- autenticação de usuários;
- níveis de acesso para administrador e funcionário;
- cadastro de fornecedores;
- cadastro de clientes;
- cadastro e controle de materiais;
- cadastro de produtos e suas variações;
- definição da ficha de consumo de materiais por variação;
- entradas e saídas de estoque;
- histórico de movimentações;
- controle de estoque mínimo;
- ordens de produção;
- acompanhamento das etapas:
  - Corte;
  - Bordado;
  - Costura;
  - Finalização;
  - Conclusão;
- consumo automático de materiais ao iniciar a produção;
- entrada automática de produtos acabados ao concluir itens;
- reversão controlada de etapas;
- cancelamento de ordens;
- dashboard gerencial;
- relatórios e indicadores de estoque e produção.

## Tecnologias

### Aplicação

- PHP 8.4
- Laravel 13
- MySQL 8.4
- HTML
- CSS
- Bootstrap 5
- JavaScript

### Infraestrutura

- Docker
- Docker Compose
- Nginx
- PHP-FPM

### Desenvolvimento

- Composer
- npm
- Vite
- PHPUnit
- Git

## Arquitetura Docker

O ambiente atual utiliza três instâncias da aplicação Laravel:

- `app1`
- `app2`
- `app3`

As instâncias compartilham o mesmo banco MySQL e utilizam sessões armazenadas no banco de dados.

O Nginx funciona como ponto de entrada da aplicação e distribui as requisições entre as três instâncias PHP-FPM.

```text
Cliente
   |
   v
 Nginx
   |
   +---- app1
   |
   +---- app2
   |
   +---- app3
          |
          v
        MySQL
```

Essa configuração também foi utilizada para demonstrar conceitos de sistemas distribuídos, balanceamento de carga e tolerância a falhas.

## Pré-requisitos

Para executar o projeto localmente são necessários:

- Git;
- Docker Engine;
- Docker Compose;
- Node.js;
- npm.

O projeto utiliza **Vite** para compilar os assets front-end. Não é necessário instalá-lo globalmente: ele é instalado como dependência do projeto ao executar `npm install`.

No Windows, o ambiente recomendado utiliza **WSL 2 + Ubuntu**. Usuários de Linux podem ignorar a seção de instalação do WSL e seguir diretamente para a preparação do Docker e do projeto.

## Preparando o ambiente no Windows

### 1. Instalar o WSL 2

Abra o **PowerShell como administrador** e execute:

```powershell
wsl --install
```

Após a instalação, reinicie o computador se solicitado.

Confirme o funcionamento do WSL:

```powershell
wsl --status
```

A versão padrão deve ser `2`.

Também é possível verificar as distribuições instaladas com:

```powershell
wsl --list --verbose
```

Se nenhuma distribuição estiver instalada, tente:

```powershell
wsl --install -d Ubuntu
```

ou:

```powershell
wsl --install -d Ubuntu --web-download
```

Caso a instalação da distribuição retorne `Wsl/InstallDistro/E_ACCESSDENIED`, uma alternativa é instalar o **Ubuntu diretamente pela Microsoft Store** e abri-lo pelo menu Iniciar.

Na primeira execução do Ubuntu será solicitado um nome de usuário e uma senha para o ambiente Linux.

### 2. Atualizar o Ubuntu

Dentro do terminal Ubuntu/WSL:

```bash
sudo apt update
sudo apt upgrade -y
```

### 3. Instalar Git e ferramentas básicas

```bash
sudo apt install -y git curl ca-certificates
```

Se o repositório for clonado utilizando o GitHub CLI (`gh`), certifique-se também de que ele esteja instalado e autenticado.

Na primeira conexão SSH com o GitHub pode aparecer uma mensagem semelhante a:

```text
The authenticity of host 'github.com' can't be established.
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

Após confirmar que o endereço é realmente `github.com`, responda:

```text
yes
```

### 4. Instalar o Docker no Ubuntu/WSL

> **Importante:** não é recomendado instalar o Docker neste ambiente com `sudo snap install docker`. No WSL, utilize o repositório APT oficial do Docker.

Remova configurações antigas do repositório Docker, caso existam:

```bash
sudo rm -f /etc/apt/sources.list.d/docker.sources
sudo rm -f /etc/apt/keyrings/docker.asc
```

Instale os pacotes necessários:

```bash
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
```

Adicione a chave do repositório:

```bash
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

Adicione o repositório oficial do Docker:

```bash
sudo tee /etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF
```

Atualize os repositórios e instale o Docker:

```bash
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Teste a instalação:

```bash
sudo docker run hello-world
```

Se aparecer `Hello from Docker!`, a instalação foi concluída corretamente.

### 5. Usar Docker sem `sudo`

Adicione seu usuário ao grupo `docker`:

```bash
sudo usermod -aG docker $USER
```

Depois feche o Ubuntu e, no PowerShell do Windows, execute:

```powershell
wsl --shutdown
```

Abra novamente o Ubuntu e teste:

```bash
docker ps
docker compose version
```

Os comandos devem funcionar sem `sudo`.

## Configuração inicial do projeto

### 1. Clonar o repositório

Com Git:

```bash
git clone https://github.com/Gudryan0/estoque-quimonos.git
cd estoque-quimonos
```

Ou, caso esteja utilizando o GitHub CLI:

```bash
gh repo clone Gudryan0/estoque-quimonos
cd estoque-quimonos
```

### 2. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

O arquivo `.env` contém as configurações locais da aplicação e não deve ser versionado.

### 3. Construir e iniciar os containers

```bash
docker compose up -d --build
```

Verifique se os serviços foram iniciados:

```bash
docker compose ps
```

### 4. Instalar as dependências PHP

```bash
docker compose exec app1 composer install
```

### 5. Gerar a chave da aplicação

```bash
docker compose exec app1 php artisan key:generate
```

### 6. Executar as migrations e seeders

```bash
docker compose exec app1 php artisan migrate --seed
```

Esse comando cria as tabelas do banco e executa o `DatabaseSeeder`, responsável por criar o usuário inicial de desenvolvimento.

> Se as migrations já tiverem sido executadas anteriormente, rode apenas:
>
> ```bash
> docker compose exec app1 php artisan db:seed
> ```

### 7. Instalar as dependências front-end

```bash
npm install
```

Esse comando instala as dependências definidas no `package.json`, incluindo o **Vite** utilizado pelo projeto. Portanto, não é necessário executar uma instalação global do Vite.

### 8. Compilar os assets

```bash
npm run build
```

### 9. Acessar a aplicação

A aplicação estará disponível em:

```text
http://localhost:8000
```

## Usuário inicial de desenvolvimento

O `DatabaseSeeder` deve criar um usuário administrador inicial para facilitar o primeiro acesso ao ambiente local.

Credenciais recomendadas para desenvolvimento:

```text
E-mail: admin@example.com
Senha: password
```

Esse usuário deve possuir:

```text
nivel_acesso: administrador
```

> **Importante:** essas credenciais são destinadas somente ao ambiente local de desenvolvimento. Não utilize senhas padrão em produção.

Para isso, o `DatabaseSeeder.php` pode utilizar a factory com o estado `administrador()` e sobrescrever os campos desejados:

```php
User::factory()
    ->administrador()
    ->create([
        'name' => 'Administrador',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);
```

Nesse caso, lembre-se de importar:

```php
use Illuminate\Support\Facades\Hash;
```

Depois de alterar o seeder em uma instalação já existente, execute:

```bash
docker compose exec app1 php artisan db:seed
```

Se o usuário anterior já existir e houver conflito de e-mail, ajuste ou remova o registro antes de executar novamente o seeder.

## Verificando a instalação

Confira o estado dos containers:

```bash
docker compose ps
```

Para verificar as informações do Laravel:

```bash
docker compose exec app1 php artisan about
```

Se os containers estiverem ativos e a aplicação abrir em `http://localhost:8000`, o ambiente está pronto para desenvolvimento.

## Problemas comuns

### `Wsl/InstallDistro/E_ACCESSDENIED`

Se o WSL estiver instalado, mas a instalação do Ubuntu falhar com:

```text
Wsl/InstallDistro/E_ACCESSDENIED
```

verifique primeiro:

```powershell
wsl --status
wsl --list --verbose
```

Se o WSL 2 estiver ativo, instale o Ubuntu diretamente pela Microsoft Store e execute a configuração inicial por lá.

### Erro ao instalar Docker via Snap

Erros relacionados a `mount namespace`, como:

```text
cannot preserve mount namespace
unexpected eof from helper process
```

podem ocorrer ao tentar instalar o Docker pelo Snap dentro do WSL.

Remova ou ignore essa instalação e utilize o procedimento via repositório APT descrito neste README.

### `NO_PUBKEY` ao executar `apt update`

Se o repositório do Docker retornar um erro como:

```text
NO_PUBKEY ...
```

confirme se a chave existe:

```bash
ls -l /etc/apt/keyrings/docker.asc
head -n 2 /etc/apt/keyrings/docker.asc
```

O início do arquivo deve ser:

```text
-----BEGIN PGP PUBLIC KEY BLOCK-----
```

Se a chave estiver ausente ou incorreta, refaça a etapa **Instalar o Docker no Ubuntu/WSL**.

### `ViteManifestNotFoundException`

Se o Laravel retornar:

```text
Vite manifest not found at: /var/www/html/public/build/manifest.json
```

instale as dependências front-end e gere o build:

```bash
rm -rf node_modules
npm install
npm run build
```

Confirme se o arquivo foi criado:

```bash
ls public/build
```

O diretório deve conter `manifest.json` e a pasta `assets`.

### `docker: permission denied`

Se o Docker funcionar apenas com `sudo`, execute:

```bash
sudo usermod -aG docker $USER
```

Depois encerre o WSL pelo PowerShell:

```powershell
wsl --shutdown
```

Abra o Ubuntu novamente e teste:

```bash
docker ps
```

### `Host key verification failed` ao clonar pelo GitHub

Se aparecer a confirmação da autenticidade do host `github.com`, responda `yes` para adicionar a chave do host ao arquivo `known_hosts`.

Se o erro seguinte for:

```text
Permission denied (publickey)
```

será necessário configurar a autenticação SSH do GitHub ou utilizar HTTPS/GitHub CLI autenticado.

## Comandos úteis

Iniciar os containers:

```bash
docker compose up -d
```

Parar os containers:

```bash
docker compose down
```

Reconstruir os containers:

```bash
docker compose up -d --build
```

Acompanhar o estado dos containers:

```bash
docker compose ps
```

Acompanhar os logs:

```bash
docker compose logs -f
```

Limpar caches do Laravel:

```bash
docker compose exec app1 php artisan optimize:clear
```

Visualizar informações do ambiente:

```bash
docker compose exec app1 php artisan about
```

## Testes automatizados

Os testes utilizam SQLite em memória e são isolados do banco MySQL de desenvolvimento.

Execute:

```bash
docker compose exec app1 php artisan test
```

A suíte cobre, entre outros pontos:

- migrations;
- integridade de usuários;
- movimentações de estoque;
- quantidades decimais de materiais;
- estoque negativo;
- início da produção;
- consumo automático de materiais;
- conclusão da produção;
- reversão;
- cancelamento;
- regras de integridade das ordens de produção.

## Banco de dados

O ambiente principal utiliza MySQL.

As configurações são definidas pelo arquivo `.env`.

O arquivo `.env` não deve ser versionado. O repositório contém somente `.env.example` como referência de configuração.

## Estrutura de produção

O fluxo produtivo utilizado pelo sistema é:

```text
Corte
  ↓
Bordado
  ↓
Costura
  ↓
Finalização
  ↓
Concluído
```

Cada item da ordem possui sua própria etapa atual, permitindo que diferentes produtos de uma mesma ordem avancem independentemente.

## Observações

O projeto é desenvolvido como trabalho acadêmico e atualmente utiliza configuração voltada ao ambiente local de desenvolvimento.

Informações sensíveis, arquivos `.env`, dependências instaladas e backups locais não fazem parte do controle de versão.
