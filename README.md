# MY FIGHT co

Sistema web para controle de estoque e acompanhamento da produção de quimonos em uma pequena confecção.

O projeto faz parte do Trabalho de Conclusão de Curso de Ciência da Computação da Universidade Paulista (UNIP).

## Objetivo

Centralizar o controle de materiais, produtos acabados, movimentações de estoque e ordens de produção, substituindo controles manuais e facilitando o acompanhamento do processo produtivo.

Além do controle de estoque, o sistema integra o fluxo de produção por lotes, permitindo acompanhar o consumo de materiais, o avanço das etapas produtivas e a entrada dos produtos concluídos no estoque.

## Principais funcionalidades

O sistema possui:

- autenticação de usuários;
- níveis de acesso para administrador e funcionário;
- controle de usuários ativos e inativos;
- bloqueio de login para usuários inativos;
- encerramento da sessão de um usuário caso sua conta seja inativada enquanto estiver conectado;
- proteção contra inativação da própria conta;
- proteção contra exclusão, inativação ou rebaixamento do último administrador ativo;
- preservação de usuários vinculados ao histórico do sistema;
- cadastro de fornecedores;
- cadastro de clientes;
- cadastro e controle de materiais;
- ativação e inativação de materiais;
- cadastro de produtos e suas variações;
- ativação e inativação de produtos;
- ativação e inativação individual ou em massa das variações;
- bloqueio da inativação de produtos ou variações envolvidos em ordens de produção ativas;
- proteção da identidade de variações já utilizadas em produção, impedindo alterações posteriores de tamanho e cor;
- definição da ficha de consumo de materiais por variação;
- entradas e saídas de estoque;
- histórico de movimentações;
- controle de estoque mínimo;
- validação para impedir estoque negativo;
- ordens de produção compostas por um ou mais lotes;
- lotes com múltiplas variações de tamanho do mesmo produto e cor;
- associação opcional de cliente por lote;
- registro livre do responsável pela costura;
- acompanhamento das etapas:
  - Corte;
  - Bordado;
  - Costura;
  - Finalização;
  - Conclusão;
- consumo automático de materiais ao iniciar a produção;
- consumo agregado de materiais quando um lote possui múltiplas variações;
- entrada automática dos produtos acabados no estoque ao concluir lotes;
- reversão controlada de etapas;
- bloqueio da reabertura de ordens concluídas;
- cancelamento controlado de ordens;
- registro histórico de cancelamentos;
- preservação dos materiais já consumidos quando uma ordem em produção é cancelada;
- dashboard gerencial;
- relatórios e indicadores de estoque e produção;
- estimativa de demanda baseada no histórico de movimentações.

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

As três instâncias compartilham o mesmo banco MySQL. As sessões e o cache da aplicação são armazenados no banco de dados, permitindo que as diferentes instâncias utilizem o mesmo estado compartilhado.

O Nginx funciona como ponto de entrada da aplicação e distribui as requisições entre as três instâncias PHP-FPM.

```text
                   +--> app1 --+
Cliente --> Nginx -+--> app2 --+--> MySQL
                   +--> app3 --+
```

Essa configuração também foi utilizada para demonstrar conceitos de sistemas distribuídos, balanceamento de carga, tolerância a falhas e escalabilidade horizontal da camada de aplicação.

> Os três containers Laravel são executados na mesma máquina física durante os experimentos. Portanto, os resultados representam o comportamento da aplicação nesse ambiente local distribuído por containers e não equivalem a uma implantação em três servidores físicos independentes.

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

Caso opte por utilizar SSH para acessar o GitHub, na primeira conexão pode aparecer uma mensagem semelhante a:

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
sudo tee /etc/apt/sources.list.d/docker.sources <<EOF_DOCKER
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF_DOCKER
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

O `DatabaseSeeder` cria um usuário administrador inicial para facilitar o primeiro acesso ao ambiente local.

Credenciais:

```text
E-mail: admin@estoque.com
Senha: Admin123!
```

O usuário possui nível de acesso `administrador` e é criado como usuário ativo.

> **Importante:** essas credenciais são destinadas exclusivamente ao ambiente acadêmico/local de desenvolvimento. Não utilize credenciais padrão em produção.

## Controle de usuários

O sistema possui dois níveis de acesso:

- `administrador`;
- `funcionario`.

Administradores podem acessar o gerenciamento de usuários e os relatórios administrativos.

Cada usuário também possui um status de acesso:

- `ativo`;
- `inativo`.

Um usuário inativo permanece registrado para preservar movimentações, ordens e históricos vinculados a ele, mas não pode iniciar uma nova sessão.

Caso uma conta seja inativada enquanto o usuário já estiver conectado, a sessão é encerrada na próxima requisição protegida.

Para preservar o acesso administrativo ao sistema:

- o usuário não pode inativar a própria conta;
- o último administrador ativo não pode ser inativado;
- o último administrador ativo não pode ser rebaixado para funcionário;
- o último administrador ativo não pode ser excluído.

## Estrutura de estoque

O estoque trabalha principalmente com dois grupos:

### Materiais

Materiais representam os insumos utilizados na produção, como tecidos, linhas e outros componentes.

Cada material pode possuir:

- categoria;
- tipo;
- cor;
- unidade de medida;
- quantidade atual;
- estoque mínimo;
- fornecedor opcional;
- status ativo/inativo.

Materiais inativos permanecem no histórico, mas não podem ser utilizados em novas movimentações manuais nem no início de novas produções.

### Produtos e variações

Um produto representa o modelo principal, enquanto suas variações representam combinações específicas de tamanho e cor.

Exemplo:

```text
Produto: Kimono Tradicional
├── A1 / Branco
├── A2 / Branco
├── A3 / Branco
└── A2 / Azul
```

Cada variação possui seu próprio:

- saldo em estoque;
- estoque mínimo;
- status ativo/inativo;
- ficha de consumo de materiais.

Quando uma variação já foi utilizada em uma ordem de produção, seu tamanho e sua cor passam a ser tratados como identidade histórica e não podem mais ser alterados. O estoque mínimo continua editável.

## Movimentações de estoque

As movimentações podem representar entradas e saídas de produtos ou materiais.

O sistema diferencia movimentações manuais de movimentações geradas automaticamente pelo processo produtivo.

Entre as validações existentes estão:

- bloqueio de saída maior que o estoque disponível;
- bloqueio de movimentações manuais para materiais inativos;
- bloqueio de movimentações manuais para variações inativas;
- controle de quantidades inteiras ou decimais conforme a unidade do material;
- limite de três casas decimais para materiais que aceitam quantidade fracionária.

Movimentações automáticas relacionadas à produção permanecem associadas à ordem e ao lote responsáveis pela operação.

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

Cada ordem de produção pode possuir vários lotes.

Cada lote está associado a um produto e uma cor e pode possuir um cliente opcional. Dentro do lote são registradas uma ou mais variações de tamanho, cada uma com sua própria quantidade.

```text
Ordem de Produção
├── Lote 1
│   ├── Produto
│   ├── Cor
│   ├── Cliente opcional
│   └── Variações
│       ├── A1 → quantidade
│       ├── A2 → quantidade
│       └── A3 → quantidade
│
└── Lote 2
    └── ...
```

Todas as variações pertencentes ao mesmo lote avançam juntas pelas etapas de produção.

Lotes diferentes de uma mesma ordem possuem etapas independentes e podem avançar separadamente pelo fluxo produtivo.

### Início da produção

Ao iniciar uma ordem, o sistema calcula o consumo necessário com base na ficha de materiais de cada variação.

Quando diferentes variações utilizam o mesmo material, o consumo é agregado antes da baixa no estoque.

A produção não é iniciada caso algum material necessário esteja inativo ou não possua quantidade suficiente.

### Conclusão

Quando um lote chega à conclusão, as quantidades produzidas são adicionadas ao estoque de cada variação correspondente.

A ordem é concluída quando todos os seus lotes chegam ao estado concluído.

Uma ordem concluída é tratada como estado terminal e não pode ser reaberta por reversão nem cancelada posteriormente.

### Reversão

Enquanto a ordem ainda está em produção, etapas podem ser revertidas conforme as regras do fluxo.

Quando a reversão exige remover do estoque produtos que haviam sido adicionados durante a conclusão de um lote, o sistema valida previamente todas as variações envolvidas para impedir saldos negativos ou reversões parciais.

### Cancelamento

Ordens planejadas ou em produção podem ser canceladas conforme as regras de integridade.

O cancelamento registra:

- motivo;
- data e hora;
- usuário responsável;
- histórico de cancelamento para os lotes envolvidos.

Quando uma ordem já iniciada é cancelada, os materiais anteriormente consumidos não são devolvidos automaticamente ao estoque.

Caso a ordem ainda em produção possua um lote concluído, esse lote deve primeiro ser revertido para Finalização antes do cancelamento.

## Relatórios e indicadores

O sistema possui dashboard e relatórios administrativos para apoiar o acompanhamento do estoque e da produção.

Entre os dados apresentados estão indicadores de materiais, produtos, movimentações, produção e níveis de estoque.

A estimativa de demanda utiliza o histórico registrado pelo próprio sistema. Trata-se de uma projeção baseada em dados históricos e não de um modelo de inteligência artificial ou aprendizado de máquina.

## Data e hora

A aplicação mantém timestamps internos em UTC.

Datas e horários apresentados ao usuário são convertidos para o fuso horário de negócio configurado por `APP_BUSINESS_TIMEZONE`, atualmente:

```text
America/Sao_Paulo
```

A configuração global da aplicação permanece em:

```text
APP_TIMEZONE=UTC
```

Datas puramente calendáricas, como a data prevista de uma ordem de produção, não passam por conversão de fuso horário.

## Testes automatizados

Os testes utilizam SQLite em memória e são isolados do banco MySQL de desenvolvimento.

Execute:

```bash
docker compose exec app1 php artisan test
```

A suíte cobre, entre outros pontos:

- uso de SQLite em memória no ambiente de testes;
- estrutura final das migrations;
- criação de usuários por factory;
- autenticação de usuários ativos;
- bloqueio de login de usuários inativos;
- encerramento de sessões de contas inativadas;
- autorização administrativa;
- integridade de usuários;
- proteção do último administrador ativo;
- preservação de usuários com histórico;
- movimentações de estoque;
- quantidades decimais de materiais;
- bloqueio de estoque negativo;
- bloqueio de movimentações com itens inativos;
- início da produção;
- consumo automático e agregado de materiais;
- impedimento do início quando há material inativo ou estoque insuficiente;
- lotes com múltiplas variações de tamanho;
- entrada de estoque de produtos acabados por variação;
- conclusão da produção;
- reversão controlada de lotes;
- bloqueio de reabertura de ordens concluídas;
- cancelamento de ordens;
- registro de histórico de cancelamento;
- regras de integridade das ordens de produção.

Para verificar também a compilação das views Blade:

```bash
docker compose exec app1 php artisan view:cache
```

Para verificar problemas de whitespace nas alterações versionadas:

```bash
git diff --check
```

## Banco de dados

O ambiente principal utiliza MySQL 8.4.

As configurações são definidas pelo arquivo `.env`.

O arquivo `.env` não deve ser versionado. O repositório contém somente `.env.example` como referência de configuração.

No ambiente atual, sessões e cache utilizam o banco de dados compartilhado entre as instâncias da aplicação.

## Estrutura principal do projeto

```text
app/
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Models/
└── Providers/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
├── console.php
└── web.php

tests/
├── Feature/
└── TestCase.php

scripts/
evidencias/
```

## Experimentos de sistemas distribuídos

O diretório `scripts/` contém ferramentas utilizadas para demonstrar e avaliar a arquitetura distribuída.

### Demonstração de distribuição e tolerância a falhas

```bash
bash scripts/demonstrar-distribuicao.sh
```

O script demonstra:

- distribuição de requisições entre `app1`, `app2` e `app3`;
- continuidade do serviço após a falha de um nó;
- continuidade do serviço com apenas um nó Laravel disponível;
- recuperação dos nós;
- retorno dos nós recuperados ao balanceamento;
- transparência para o cliente, que utiliza sempre a mesma URL.

### Teste de escalabilidade

```bash
bash scripts/testar-escalabilidade.sh
```

Compara o comportamento da aplicação utilizando uma, duas e três instâncias Laravel sob a mesma carga.

### Teste de carga e escalabilidade

```bash
bash scripts/testar-carga-escalabilidade.sh
```

Compara uma, duas e três instâncias Laravel em diferentes níveis de concorrência.

Os resultados dos experimentos são armazenados no diretório `evidencias/` em arquivos TXT e CSV.

Nos experimentos realizados, todas as instâncias Laravel, o Nginx e o MySQL foram executados na mesma máquina física. Portanto, os resultados devem ser interpretados como evidências do comportamento da arquitetura no ambiente experimental local.

O banco MySQL permanece compartilhado e não foi replicado nesses experimentos. A tolerância a falhas demonstrada refere-se à camada de aplicação Laravel enquanto o Nginx e o banco de dados permanecem disponíveis.

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

Executar os testes:

```bash
docker compose exec app1 php artisan test
```

Compilar as views Blade:

```bash
docker compose exec app1 php artisan view:cache
```

Compilar os assets front-end:

```bash
npm run build
```

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

## Observações finais

O projeto é desenvolvido como trabalho acadêmico e atualmente utiliza configuração voltada ao ambiente local de desenvolvimento e demonstração.

Informações sensíveis, arquivos `.env`, dependências instaladas, caches, logs, bancos SQLite locais e backups não fazem parte do controle de versão.

As regras de integridade permanecem implementadas no backend mesmo quando a interface também impede ou sinaliza determinada ação. Dessa forma, alterações manuais no HTML ou requisições diretas não substituem as validações da aplicação.
