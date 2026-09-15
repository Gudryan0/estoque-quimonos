#!/usr/bin/env bash

set -euo pipefail

PROJECT_DIR="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/.."
    pwd
)"

cd "$PROJECT_DIR"

URL="http://localhost:8000/login"

REQUISICOES=300
CONCORRENCIA=30
REPETICOES=3

DATA="$(
    date '+%Y%m%d-%H%M%S'
)"

CSV="evidencias/escalabilidade-$DATA.csv"
RELATORIO="evidencias/escalabilidade-$DATA.txt"

mkdir -p evidencias

BACKUP_NGINX="$(
    mktemp
)"

cp nginx.conf "$BACKUP_NGINX"

restaurar()
{
    echo
    echo "Restaurando configuração original do Nginx..."

    cp \
        "$BACKUP_NGINX" \
        nginx.conf

    rm -f \
        "$BACKUP_NGINX"

    if docker compose exec \
        nginx nginx -t \
        >/dev/null 2>&1; then

        docker compose exec \
            nginx nginx -s reload \
            >/dev/null 2>&1 \
            || true
    fi
}

trap restaurar EXIT

gerar_nginx()
{
    local quantidade="$1"

    {
        cat <<'EOF'
upstream laravel_backend {
    zone laravel_backend 64k;

EOF

        echo \
"    server app1:9000 max_fails=1 fail_timeout=5s;"

        if (( quantidade >= 2 )); then
            echo \
"    server app2:9000 max_fails=1 fail_timeout=5s;"
        fi

        if (( quantidade >= 3 )); then
            echo \
"    server app3:9000 max_fails=1 fail_timeout=5s;"
        fi

        cat <<EOF
}

server {
    listen 80;
    server_name localhost;

    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        try_files \$uri =404;

        fastcgi_pass laravel_backend;
        fastcgi_index index.php;

        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;

        fastcgi_connect_timeout 1s;

        fastcgi_next_upstream error timeout invalid_header;
        fastcgi_next_upstream_tries $quantidade;
        fastcgi_next_upstream_timeout 3s;

        add_header X-Upstream-Addr \$upstream_addr always;
        add_header X-Upstream-Status \$upstream_status always;
        add_header X-Upstream-Response-Time \$upstream_response_time always;
    }

    location ~ /\\.ht {
        deny all;
    }
}
EOF
    } > nginx.conf

    docker compose exec \
        nginx nginx -t

    docker compose exec \
        nginx nginx -s reload

    sleep 2
}

executar_cenario()
{
    local quantidade="$1"
    local nome="$2"

    echo
    echo "============================================================"
    echo " $nome"
    echo "============================================================"
    echo

    gerar_nginx \
        "$quantidade"

    for repeticao in \
        $(seq 1 "$REPETICOES"); do

        echo
        echo "------------------------------------------------------------"
        echo "Repetição $repeticao de $REPETICOES"
        echo "------------------------------------------------------------"
        echo

        python3 \
            scripts/benchmark_http.py \
            --url "$URL" \
            --requests "$REQUISICOES" \
            --concurrency "$CONCORRENCIA" \
            --warmup 20 \
            --scenario "$nome" \
            --repetition "$repeticao" \
            --csv "$CSV"

        sleep 2
    done
}

docker compose start \
    mysql \
    app1 \
    app2 \
    app3 \
    nginx \
    >/dev/null

sleep 2

{
    echo "============================================================"
    echo " TESTE DE DESEMPENHO E ESCALABILIDADE"
    echo "============================================================"
    echo
    echo "Data:"
    echo "  $(date '+%d/%m/%Y %H:%M:%S')"
    echo
    echo "URL:"
    echo "  $URL"
    echo
    echo "Requisições por repetição:"
    echo "  $REQUISICOES"
    echo
    echo "Concorrência:"
    echo "  $CONCORRENCIA"
    echo
    echo "Repetições por cenário:"
    echo "  $REPETICOES"

    executar_cenario \
        1 \
        "1 nó Laravel"

    executar_cenario \
        2 \
        "2 nós Laravel"

    executar_cenario \
        3 \
        "3 nós Laravel"

    echo
    echo "============================================================"
    echo " TESTE CONCLUÍDO"
    echo "============================================================"
    echo
    echo "CSV:"
    echo "  $CSV"
    echo
    echo "Configuração original do Nginx será restaurada."
} | tee "$RELATORIO"

echo
echo "Relatório:"
echo "  $RELATORIO"

echo
echo "Dados CSV:"
echo "  $CSV"
