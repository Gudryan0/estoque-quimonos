#!/usr/bin/env python3

import argparse
import concurrent.futures
import csv
import math
import os
import statistics
import time
import urllib.error
import urllib.request
from collections import Counter


def fazer_requisicao(url):
    inicio = time.perf_counter()

    try:
        with urllib.request.urlopen(
            url,
            timeout=10
        ) as resposta:
            resposta.read()

            status = resposta.status
            erro = None

    except urllib.error.HTTPError as excecao:
        excecao.read()

        status = excecao.code
        erro = None

    except Exception as excecao:
        status = 0
        erro = type(excecao).__name__

    duracao = time.perf_counter() - inicio

    return duracao, status, erro


def percentil(valores, percentual):
    valores = sorted(valores)

    indice = math.ceil(
        percentual * len(valores)
    ) - 1

    indice = max(
        0,
        min(
            indice,
            len(valores) - 1
        )
    )

    return valores[indice]


def main():
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--url",
        required=True
    )

    parser.add_argument(
        "--requests",
        type=int,
        default=300
    )

    parser.add_argument(
        "--concurrency",
        type=int,
        default=30
    )

    parser.add_argument(
        "--warmup",
        type=int,
        default=20
    )

    parser.add_argument(
        "--scenario",
        required=True
    )

    parser.add_argument(
        "--repetition",
        type=int,
        required=True
    )

    parser.add_argument(
        "--csv",
        required=True
    )

    args = parser.parse_args()

    print(
        f"Aquecendo com "
        f"{args.warmup} requisições..."
    )

    for _ in range(args.warmup):
        fazer_requisicao(
            args.url
        )

    print(
        f"Executando "
        f"{args.requests} requisições "
        f"com concorrência "
        f"{args.concurrency}..."
    )

    inicio_total = time.perf_counter()

    with concurrent.futures.ThreadPoolExecutor(
        max_workers=args.concurrency
    ) as executor:

        futuros = [
            executor.submit(
                fazer_requisicao,
                args.url
            )
            for _ in range(
                args.requests
            )
        ]

        resultados = [
            futuro.result()
            for futuro in
            concurrent.futures.as_completed(
                futuros
            )
        ]

    tempo_total = (
        time.perf_counter()
        - inicio_total
    )

    tempos = [
        resultado[0]
        for resultado in resultados
    ]

    statuses = Counter(
        resultado[1]
        for resultado in resultados
    )

    erros = [
        resultado[2]
        for resultado in resultados
        if resultado[2] is not None
    ]

    sucessos = sum(
        quantidade
        for status, quantidade
        in statuses.items()
        if 200 <= status < 400
    )

    requisicoes_por_segundo = (
        args.requests
        / tempo_total
    )

    media_ms = (
        statistics.mean(tempos)
        * 1000
    )

    mediana_ms = (
        statistics.median(tempos)
        * 1000
    )

    p95_ms = (
        percentil(
            tempos,
            0.95
        )
        * 1000
    )

    minimo_ms = (
        min(tempos)
        * 1000
    )

    maximo_ms = (
        max(tempos)
        * 1000
    )

    status_texto = ";".join(
        f"{status}:{quantidade}"
        for status, quantidade
        in sorted(
            statuses.items()
        )
    )

    print()
    print(
        f"Cenário: "
        f"{args.scenario}"
    )

    print(
        f"Repetição: "
        f"{args.repetition}"
    )

    print(
        f"Sucessos: "
        f"{sucessos}/"
        f"{args.requests}"
    )

    print(
        f"Erros: "
        f"{len(erros)}"
    )

    print(
        f"Tempo total: "
        f"{tempo_total:.3f} s"
    )

    print(
        f"Requisições/s: "
        f"{requisicoes_por_segundo:.2f}"
    )

    print(
        f"Média: "
        f"{media_ms:.2f} ms"
    )

    print(
        f"Mediana: "
        f"{mediana_ms:.2f} ms"
    )

    print(
        f"P95: "
        f"{p95_ms:.2f} ms"
    )

    print(
        f"Mínimo: "
        f"{minimo_ms:.2f} ms"
    )

    print(
        f"Máximo: "
        f"{maximo_ms:.2f} ms"
    )

    print(
        f"Status HTTP: "
        f"{status_texto}"
    )

    arquivo_existe = (
        os.path.exists(args.csv)
        and
        os.path.getsize(args.csv) > 0
    )

    with open(
        args.csv,
        "a",
        newline="",
        encoding="utf-8"
    ) as arquivo:

        writer = csv.writer(
            arquivo
        )

        if not arquivo_existe:
            writer.writerow([
                "cenario",
                "repeticao",
                "requisicoes",
                "concorrencia",
                "sucessos",
                "erros",
                "tempo_total_s",
                "requisicoes_por_segundo",
                "media_ms",
                "mediana_ms",
                "p95_ms",
                "minimo_ms",
                "maximo_ms",
                "status_http",
            ])

        writer.writerow([
            args.scenario,
            args.repetition,
            args.requests,
            args.concurrency,
            sucessos,
            len(erros),
            f"{tempo_total:.6f}",
            f"{requisicoes_por_segundo:.6f}",
            f"{media_ms:.6f}",
            f"{mediana_ms:.6f}",
            f"{p95_ms:.6f}",
            f"{minimo_ms:.6f}",
            f"{maximo_ms:.6f}",
            status_texto,
        ])


if __name__ == "__main__":
    main()
