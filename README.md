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

## Requisitos

Para executar o projeto no ambiente de desenvolvimento:

- Docker
- Docker Compose
- Node.js
- npm

## Configuração inicial

Clone o repositório e entre na pasta do projeto.

Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

Construa e inicie os containers:

```bash
docker compose up -d --build
```

Instale as dependências PHP:

```bash
docker compose exec app1 composer install
```

Gere a chave da aplicação:

```bash
docker compose exec app1 php artisan key:generate
```

Execute as migrations:

```bash
docker compose exec app1 php artisan migrate
```

Instale as dependências front-end:

```bash
npm install
```

Compile os assets:

```bash
npm run build
```

A aplicação estará disponível em:

```text
http://localhost:8000
```

## Comandos úteis

Limpar caches do Laravel:

```bash
docker compose exec app1 php artisan optimize:clear
```

Visualizar informações do ambiente:

```bash
docker compose exec app1 php artisan about
```

Acompanhar os containers:

```bash
docker compose ps
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
