#!/usr/bin/env python3
"""
formar_grupos.py

Cria 3 rodadas de grupos ("trabalho_1", "trabalho_2", "trabalho_3")
para uma turma com t alunos e g grupos por trabalho.
Pode utilizar uma seed opcional para reprodutibilidade.

Saída: grupos.csv com colunas:
trabalho;grupo;tema;alunos

Uso:
    python formar_grupos.py --alunos 30 --grupos 5 --seed 42
"""

import argparse
import csv
import itertools
import sys
import random


# ============================
# Funções auxiliares
# ============================

def gerar_ids_alunos(t):
    """Retorna lista: aluno_1 ... aluno_t"""
    return [f"aluno_{i}" for i in range(1, t + 1)]


def gerar_ids_temas(g):
    """Retorna lista: tema_1 ... tema_g"""
    return [f"tema_{i}" for i in range(1, g + 1)]


def tamanhos_grupos_balanceados(t, g):
    """Retorna tamanhos balanceados para g grupos."""
    if g <= 0:
        raise ValueError("g deve ser > 0")

    base = t // g
    resto = t % g
    return [base + (1 if i < resto else 0) for i in range(g)]


def dividir_por_tamanhos(lista, tamanhos):
    """Divide lista em blocos com os tamanhos dados."""
    resultado = []
    i = 0
    for tam in tamanhos:
        resultado.append(lista[i:i + tam])
        i += tam
    return resultado


# ============================
# Trabalho 1
# ============================

def gerar_trabalho_1(alunos, temas, rng):
    """Gera o trabalho_1 aleatório e balanceado."""
    tamanhos = tamanhos_grupos_balanceados(len(alunos), len(temas))

    perm = alunos[:]
    rng.shuffle(perm)
    blocos = dividir_por_tamanhos(perm, tamanhos)

    trabalho = []
    for i, bloco in enumerate(blocos):
        trabalho.append({
            "grupo": i + 1,
            "tema": temas[i],
            "alunos": bloco
        })
    return trabalho


# ============================
# Métricas de repetição
# ============================

def mapa_grupos_anteriores(trabalho):
    """Mapeia aluno -> grupo anterior."""
    mapa = {}
    for info in trabalho:
        for aluno in info["alunos"]:
            mapa[aluno] = info["grupo"]
    return mapa


def metrica_pares_repetidos(grupos, mapa_anterior):
    """Conta pares que permaneceram juntos."""
    total = 0
    for grupo in grupos:
        for a, b in itertools.combinations(grupo, 2):
            if mapa_anterior.get(a) == mapa_anterior.get(b):
                total += 1
    return total


# ============================
# Trabalho 2
# ============================

def gerar_trabalho_2(alunos, temas, trabalho_1, rng, max_tentativas=5000):
    """
    Gera trabalho_2 tentando:
    - evitar repetir tema
    - minimizar alunos repetidos no mesmo grupo
    """

    tamanhos = tamanhos_grupos_balanceados(len(alunos), len(temas))
    mapa_grupo_anterior = mapa_grupos_anteriores(trabalho_1)

    tema_anterior = {}
    for info in trabalho_1:
        for aluno in info["alunos"]:
            tema_anterior[aluno] = info["tema"]

    melhor = None
    melhor_metrica = None
    encontrou_sem_repeticao = False

    for _ in range(max_tentativas):
        perm = alunos[:]
        rng.shuffle(perm)
        blocos = dividir_por_tamanhos(perm, tamanhos)

        viola = False
        for i, bloco in enumerate(blocos):
            tema = temas[i]
            if any(tema_anterior[a] == tema for a in bloco):
                viola = True
                break

        metrica = metrica_pares_repetidos(blocos, mapa_grupo_anterior)

        if not viola:
            if (melhor_metrica is None) or (metrica < melhor_metrica):
                melhor = blocos
                melhor_metrica = metrica
                encontrou_sem_repeticao = True
            if metrica == 0:
                break
        else:
            if not encontrou_sem_repeticao:
                if (melhor_metrica is None) or (metrica < melhor_metrica):
                    melhor = blocos
                    melhor_metrica = metrica

    if melhor is None:
        print("AVISO: não foi possível gerar trabalho_2 válido.", file=sys.stderr)
        melhor = dividir_por_tamanhos(alunos, tamanhos)

    trabalho_2 = []
    for i, bloco in enumerate(melhor):
        trabalho_2.append({
            "grupo": i + 1,
            "tema": temas[i],
            "alunos": bloco
        })

    return trabalho_2


# ============================
# Trabalho 3
# ============================

def gerar_trabalho_3(trab1, trab2, temas, rng):
    """
    Trabalho 3:
    - balanceamento perfeito (restrição rígida)
    - determinístico com seed
    - cada aluno recebe tema já visto
    """

    # ordem determinística
    alunos = sorted(a for info in trab1 for a in info["alunos"])
    tamanhos = tamanhos_grupos_balanceados(len(alunos), len(temas))

    # mapa aluno -> temas permitidos
    permitidos = {}
    for info in trab1:
        for a in info["alunos"]:
            permitidos.setdefault(a, set()).add(info["tema"])
    for info in trab2:
        for a in info["alunos"]:
            permitidos.setdefault(a, set()).add(info["tema"])

    # criar slots balanceados (exatos)
    slots = []
    for tema, tam in zip(temas, tamanhos):
        slots.extend([tema] * tam)

    rng.shuffle(slots)

    # embaralhar alunos de forma determinística
    ordem = alunos[:]
    rng.shuffle(ordem)

    # primeira atribuição
    atribuicao = {}
    sobras = []

    for aluno, tema_slot in zip(ordem, slots):
        if tema_slot in permitidos.get(aluno, set(temas)):
            atribuicao[aluno] = tema_slot
        else:
            sobras.append((aluno, tema_slot))

    # capacidade restante por tema
    contagem = {t: 0 for t in temas}
    for tema in atribuicao.values():
        contagem[tema] += 1

    capacidade = {t: tam for t, tam in zip(temas, tamanhos)}

    # fase de reparo determinística
    for aluno, _ in sobras:
        for tema in temas:  # ordem fixa = determinismo
            if (
                tema in permitidos.get(aluno, set(temas))
                and contagem[tema] < capacidade[tema]
            ):
                atribuicao[aluno] = tema
                contagem[tema] += 1
                break
        else:
            # fallback impossível (só por segurança)
            for tema in temas:
                if contagem[tema] < capacidade[tema]:
                    atribuicao[aluno] = tema
                    contagem[tema] += 1
                    break

    # montar grupos finais
    grupos = {t: [] for t in temas}
    for aluno in alunos:
        grupos[atribuicao[aluno]].append(aluno)

    trabalho_3 = []
    for i, tema in enumerate(temas):
        trabalho_3.append({
            "grupo": i + 1,
            "tema": tema,
            "alunos": grupos[tema]
        })

    return trabalho_3


# ============================
# Exportação CSV
# ============================

def exportar_csv(nome_arquivo, trabalhos):
    """Salva os trabalhos no formato especificado."""
    with open(nome_arquivo, "w", newline="", encoding="utf-8") as f:
        escritor = csv.writer(f, delimiter=';')
        escritor.writerow(["trabalho", "grupo", "tema", "alunos"])

        for i, trabalho in enumerate(trabalhos, start=1):
            for grupo in trabalho:
                alunos_str = ",".join(grupo["alunos"])
                escritor.writerow([i, grupo["grupo"], grupo["tema"], alunos_str])


# ============================
# CLI
# ============================

def ler_argumentos():
    parser = argparse.ArgumentParser(description="Gerador de grupos para trabalhos.")
    parser.add_argument("-a", "--alunos", type=int, required=True, help="Número total de alunos")
    parser.add_argument("-g", "--grupos", type=int, required=True, help="Número de grupos")
    parser.add_argument("--seed", type=int, default=None, help="Seed opcional")
    parser.add_argument("--max-attempts", type=int, default=5000, help="Tentativas no trabalho 2")
    parser.add_argument("--out", type=str, default="grupos.csv", help="Arquivo de saída")
    return parser.parse_args()


def main():
    args = ler_argumentos()

    if args.alunos <= 0 or args.grupos <= 0:
        print("t e g devem ser positivos.", file=sys.stderr)
        sys.exit(1)

    rng = random.Random(args.seed)

    alunos = gerar_ids_alunos(args.alunos)
    temas = gerar_ids_temas(args.grupos)

    trab1 = gerar_trabalho_1(alunos, temas, rng)
    trab2 = gerar_trabalho_2(alunos, temas, trab1, rng, args.max_attempts)
    trab3 = gerar_trabalho_3(trab1, trab2, temas, rng)

    exportar_csv(args.out, [trab1, trab2, trab3])

    print(f"Grupos gerados em '{args.out}'.")


if __name__ == "__main__":
    main()