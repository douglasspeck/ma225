<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>MA225 - Avaliação</title>

        <?php include('../assets/php/head.php'); ?>

        <link rel="preload" as="style" onload="this.remove();" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <!-- SEO -->
        <meta name="author" content="Speck">
        <meta name="description" content="Informações sobre o Processo Avaliativo da Disciplina MA225 oferecida pelo IMECC-Unicamp">
        <meta name="keywords" content="ma225, livros didáticos, materiais didáticos, matemática, imecc, unicamp">
        <link rel="canonical" href="https://ime.unicamp.br/~ma225/avaliacao/">
    </head>
    <body>
        <?php include('../assets/php/header.php'); ?>
        <main id="assessments">
            <section>
                <h1>Avaliação</h1>
                <p>A avaliação da disciplina é baseada em cinco tarefas investigativas, realizadas em grupo ao longo do semestre. Cada tarefa propõe uma forma diferente de analisar ou produzir materiais didáticos, articulando teoria, prática e reflexão crítica.</p>
            </section>
            <section class="two-cols">
                <section class="col">
                    <section id="final-grade" class="formula">
                        <div class="equation">
                            <div class="left-part">
                                <span>Média</span>
                            </div>
                            <div class="right-part fraction">
                                <div class="numerator">
                                    <span class="task-weight">4</span> <span id="gT1" class="task-grade">T<sub>1</sub></span> <span>+</span>
                                    <span class="task-weight">4</span> <span id="gT2" class="task-grade">T<sub>2</sub></span> <span>+</span>
                                    <span class="task-weight">4</span> <span id="gT3" class="task-grade">T<sub>3</sub></span> <span>+</span>
                                    <span class="task-weight">5</span> <span id="gT4" class="task-grade">T<sub>4</sub></span> <span>+</span>
                                    <span class="task-weight">3</span> <span id="gT5" class="task-grade">T<sub>5</sub></span>
                                </div>
                                <div class="denominator">
                                    <span>20</span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section id="task-grade" class="formula">
                        <div class="equation">
                            <div class="left-part">
                                <span>T<sub>i</sub></span>
                            </div>
                            <div class="right-part">
                                <div class="fraction">
                                    <div class="numerator">
                                        <span id="tcA" class="formula-task-comp">A</span> +
                                        <span>3</span> <span id="tcR" class="formula-task-comp">R</span>
                                    </div>
                                    <div class="denominator">
                                        <span>4</span>
                                    </div>
                                </div>
                                <span class="mult"></span>
                                <span id="tcG" class="formula-task-comp">G</span>
                            </div>
                        </div>
                    </section>
                    <section id="task-components">
                        <h2>Avaliação por tarefa:</h2>
                        <ol class="comp-list">
                            <li class="component" id="task-comp-A">
                                <h3><span>A</span>presentação</h3>
                                <p>Avaliação da apresentação oral do trabalho.</p>
                            </li>
                            <li class="component" id="task-comp-R">
                                <h3><span>R</span>elatório</h3>
                                <p>Avaliação do relatório escrito entregue pelo grupo.</p>
                            </li>
                            <li class="component" id="task-comp-G">
                                <h3>Moda do <span>G</span>rupo</h3>
                                <p>Cada integrante avalia a contribuição dos colegas. A partir dessas avaliações, calcula-se um coeficiente coletivo (a moda do grupo) que ajusta a nota individual.</p>
                            </li>
                        </ol>
                    </section>
                </section>
                <section class="col" id="five-tasks">
                    <h2>As cinco tarefas:</h2>
                    <ol class="comp-list">
                        <li class="component task" id="T1">
                            <h3><span>T<sub>1</sub></span> Análise vertical</h3>
                            <p>Estudo aprofundado de um livro didático específico, com desenvolvimento de uma metodologia própria de avaliação.</p>
                        </li>
                        <li class="component task" id="T2">
                            <h3><span>T<sub>2</sub></span> Análise horizontal</h3>
                            <p>Comparação entre dois livros a partir de critérios comuns, buscando identificar diferenças de abordagem e qualidade.</p>
                        </li>
                        <li class="component task" id="T3">
                            <h3><span>T<sub>3</sub></span> Análise de livro estrangeiro</h3>
                            <p>Investigação de um material didático de outro país, considerando seu contexto educacional e comparando à realidade brasileira.</p>
                        </li>
                        <li class="component task" id="T4">
                            <h3><span>T<sub>4</sub></span> Produção de material didático</h3>
                            <p>Elaboração de um capítulo ou sequência didática autoral (aprox. 30 páginas), aplicando os aprendizados das análises anteriores.</p>
                        </li>
                        <li class="component task" id="T5">
                            <h3><span>T<sub>5</sub></span> Revisão por pares</h3>
                            <p>Avaliação crítica do material produzido por outro grupo, com foco em feedback qualificado e fundamentado.</p>
                        </li>
                    </ol>
                </section>
            </section>
        </main>
        <?php include('../assets/php/footer.php'); ?>
        <script src="../assets/js/detectScroll.js"></script>
    </body>
</html>