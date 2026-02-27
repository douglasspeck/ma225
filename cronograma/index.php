<?php

    include_once('../assets/php/parse_data.php');

    function render_assessments($assessments) {

        $today = new DateTime('today');

        $rows = 1;
        foreach ($assessments as $a) {
            $schedule = $a['schedule'] ?? [];
            $dates = [];
            foreach ($schedule as $s) {
                if (in_array($s['date'], $dates)) { continue; }
                array_push($dates, $s['date']);
            }
            $rows = max($rows, count($dates));
        }
        $rows += 1;

        echo '<section id="assessments" style="--rows:'.$rows.'">';
        echo '<h2 class="hidden">Trabalhos</h2>';
    
        foreach ($assessments as $index => $assessment) {
    
            if (empty($assessment['id']) || empty($assessment['title'])) {
                continue;
            }
    
            $id = $assessment['id'];
            $title = $assessment['title'];
            $schedule = $assessment['schedule'] ?? [];
    
            echo '<section id="' . e($id) . '" class="assessment">';
            echo '<h3>Trabalho ' . ($index + 1) . ': ' . e($title) . '</h3>';
    
            if (!empty($schedule)) {
    
                // Ordena por data
                usort($schedule, fn($a, $b) => strcmp($a['date'], $b['date']));
    
                // Agrupa por data
                $grouped = [];
                foreach ($schedule as $item) {
                    if (empty($item['date']) || empty($item['title'])) continue;
                    $grouped[$item['date']][] = $item['title'];
                }
    
                echo '<ul>';
    
                foreach ($grouped as $date => $events) {
                    $eventDate = new DateTime($date);

                    $class = ($eventDate < $today) ? ' class="check"' : '';

                    echo "<li{$class}>";
                    echo '<span class="date">' . format_date_br($date) . '</span>';

                    foreach ($events as $eventTitle) {
                        echo '<p>' . e($eventTitle) . '</p>';
                    }

                    echo '</li>';
                }
    
                echo '</ul>';
            }
    
            echo '</section>';
        }
    
        echo '</section>';

    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>MA225 - Cronograma</title>

        <?php include('../assets/php/head.php'); ?>

        <link rel="preload" as="style" onload="this.remove();" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <!-- SEO -->
        <meta name="author" content="Speck">
        <meta name="description" content="Cronograma da Disciplina MA225 oferecida pelo IMECC-Unicamp">
        <meta name="keywords" content="ma225, livros didáticos, materiais didáticos, matemática, imecc, unicamp">
        <link rel="canonical" href="https://ime.unicamp.br/~ma225/cronograma/">
    </head>
    <body>
        <?php include('../assets/php/header.php'); ?>
        <main id="schedule">
            <section>
                <h1>Cronograma</h1>
                <p>A disciplina é fundamentalmente prática, com as aulas voltadas majoritariamente para o desenvolvimento das tarefas avaliativas.</p>
                <?php render_assessments(oferta('current.assessments', [])) ?>
                <p>Além dos encontros para elaboração dos projetos, a disciplina contará com momentos de explicação sobre os mesmos, além de exposições teóricas e outros pontos de contato institucionais.</p>
                <!--section id="calendar">
                    <h2 class="hidden">Calendário</h2>
                    <section class="month">
                        <h3>Fevereiro</h3>
                        <article class="day">
                            <h3 class="date">27/02/2026</h3>
                            <div class="content">
                                <p>Apresentação da Disciplina</p>
                            </div>
                        </article>
                    </section>
                </section-->
            </section>
            <section class="gallery center">
                <a href="https://docs.google.com/spreadsheets/d/1KdurgPGgJg-AmgexFv18L0hDdt1613ywpk2pC-opW4A/edit?usp=sharing" class="button">Calendário Completo</a>
            </section>
        </main>
        <?php include('../assets/php/footer.php'); ?>
        <script src="../assets/js/detectScroll.js"></script>
    </body>
</html>