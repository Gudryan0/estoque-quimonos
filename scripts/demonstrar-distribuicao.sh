#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

URL="http://localhost:8000/"
DATA="$(date '+%Y%m%d-%H%M%S')"
RELATORIO="evidencias/distribuicao-$DATA.txt"

mkdir -p evidencias

APP1_IP="$(
    docker inspect -f \
    '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' \
    estoque_app1
)"

APP2_IP="$(
    docker inspect -f \
    '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' \
    estoque_app2
)"

APP3_IP="$(
    docker inspect -f \
    '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' \
    estoque_app3
)"

restaurar()
{
    echo
    echo "Garantindo que todas as instâncias estejam ativas..."

    docker compose start \
        app1 \
        app2 \
        app3 \
        >/dev/null 2>&1 \
        || true
}

trap restaurar EXIT

ler_header()
{
    local arquivo="$1"
    local nome="$2"

    awk -v nome="$nome" '
        BEGIN {
            IGNORECASE = 1
        }

        index(tolower($0), tolower(nome ":")) == 1 {
            sub(/^[^:]*:[[:space:]]*/, "")
            sub(/\r$/, "")
            print
            exit
        }
    ' "$arquivo"
}

identificar_destino()
{
    local upstream="$1"
    local resultado="$upstream"

    resultado="${resultado//$APP1_IP:9000/app1}"
    resultado="${resultado//$APP2_IP:9000/app2}"
    resultado="${resultado//$APP3_IP:9000/app3}"
    resultado="${resultado//, / -> }"

    if [[ -z "$resultado" ]]; then
        resultado="desconhecido"
    fi

    echo "$resultado"
}

fazer_requisicao()
{
    local numero="$1"
    local headers
    local resultado
    local codigo
    local tempo
    local upstream
    local upstream_status
    local destino

    headers="$(mktemp)"

    resultado="$(
        curl \
            -sS \
            -D "$headers" \
            -o /dev/null \
            -w '%{http_code}|%{time_total}' \
            "$URL"
    )"

    codigo="${resultado%%|*}"
    tempo="${resultado##*|}"

    upstream="$(
        ler_header \
            "$headers" \
            "X-Upstream-Addr"
    )"

    upstream_status="$(
        ler_header \
            "$headers" \
            "X-Upstream-Status"
    )"

    rm -f "$headers"

    destino="$(
        identificar_destino \
            "$upstream"
    )"

    printf \
        "%02d | %-30s | HTTP %-3s | Total: %ss" \
        "$numero" \
        "$destino" \
        "$codigo" \
        "$tempo"

    if [[ -n "$upstream_status" ]]; then
        printf \
            " | Upstream: %s" \
            "$upstream_status"
    fi

    printf "\n"
}

{
    echo "============================================================"
    echo " DEMONSTRAÇÃO FINAL DO SISTEMA DISTRIBUÍDO"
    echo "============================================================"
    echo
    echo "Data: $(date '+%d/%m/%Y %H:%M:%S')"
    echo
    echo "Cliente:"
    echo "  $URL"
    echo
    echo "Balanceador:"
    echo "  estoque_nginx"
    echo
    echo "Nós Laravel:"
    echo "  app1 = $APP1_IP:9000"
    echo "  app2 = $APP2_IP:9000"
    echo "  app3 = $APP3_IP:9000"
    echo
    echo "Persistência compartilhada:"
    echo "  estoque_mysql"
    echo

    echo "------------------------------------------------------------"
    echo "1. BALANCEAMENTO ENTRE TRÊS NÓS"
    echo "------------------------------------------------------------"
    echo

    for i in {1..15}; do
        fazer_requisicao "$i"
    done

    echo
    echo "------------------------------------------------------------"
    echo "2. FALHA DE UM NÓ"
    echo "------------------------------------------------------------"
    echo
    echo "Desligando app3..."
    echo

    docker compose stop app3 >/dev/null

    sleep 1

    for i in {1..10}; do
        fazer_requisicao "$i"
    done

    echo
    echo "------------------------------------------------------------"
    echo "3. FALHA DE DOIS NÓS"
    echo "------------------------------------------------------------"
    echo
    echo "Desligando também app2..."
    echo

    docker compose stop app2 >/dev/null

    sleep 1

    for i in {1..5}; do
        fazer_requisicao "$i"
    done

    echo
    echo "------------------------------------------------------------"
    echo "4. RECUPERAÇÃO DOS NÓS"
    echo "------------------------------------------------------------"
    echo
    echo "Religando app2 e app3..."
    echo

    docker compose start app2 app3 >/dev/null

    sleep 6

    for i in {1..15}; do
        fazer_requisicao "$i"
    done

    echo
    echo "============================================================"
    echo " RESULTADO ESPERADO"
    echo "============================================================"
    echo
    echo "Balanceamento:"
    echo "  requisições distribuídas entre app1, app2 e app3."
    echo
    echo "Falha de um nó:"
    echo "  sistema permanece disponível com dois nós."
    echo
    echo "Falha de dois nós:"
    echo "  sistema permanece disponível através do nó restante."
    echo
    echo "Recuperação:"
    echo "  nós recuperados retornam ao balanceamento."
    echo
    echo "Transparência:"
    echo "  cliente utiliza sempre a mesma URL."
    echo
    echo "============================================================"
} | tee "$RELATORIO"

echo
echo "Relatório salvo em:"
echo "  $RELATORIO"
