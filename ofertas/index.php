<?php
    require_once dirname(__DIR__) . '/assets/php/parse_data.php';

    function offer_is_valid_id(string $id): bool {
        return $id !== 'current'
            && $id !== 'current_id'
            && preg_match('/^\d{4}(?:$|-.+)$/', $id);
    }

    function get_offer_ids(): array {
        $ids = array_values(array_filter(array_keys($GLOBALS['OFERTAS'] ?? []), 'offer_is_valid_id'));

        usort($ids, function ($a, $b) {
            preg_match('/^(\d{4})/', $a, $ma);
            preg_match('/^(\d{4})/', $b, $mb);

            $yearA = (int)($ma[1] ?? 0);
            $yearB = (int)($mb[1] ?? 0);

            if ($yearA !== $yearB) {
                return $yearB <=> $yearA;
            }

            return strnatcmp($b, $a);
        });

        return $ids;
    }

    function offer_label(array $offer, string $id): string {
        $year = (string)($offer['year'] ?? preg_replace('/^(\d{4}).*$/', '$1', $id));
        $semester = $offer['semester'] ?? null;

        if ($semester === null || $semester === '') {
            if (preg_match('/^\d{4}-(.+)$/', $id, $m)) {
                return $year . '/' . $m[1];
            }
            return $year;
        }

        return $year . '/' . $semester;
    }

    function offer_monitors_names(array $offer): array {
        $monitors = $offer['monitors'] ?? [];
        if (!is_array($monitors)) {
            return [];
        }

        $names = [];
        foreach ($monitors as $monitor) {
            if (!empty($monitor['name'])) {
                $names[] = $monitor['name'];
            }
        }

        return $names;
    }

    function offer_assessment_id(array $assessment): string {
        return trim((string)($assessment['id'] ?? $assessment[' id'] ?? ''));
    }

    function build_query_url(array $overrides = []): string {
        $params = $_GET;

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($params[$key]);
            } else {
                $params[$key] = $value;
            }
        }

        $query = http_build_query($params);
        return $query !== '' ? ('?' . $query) : '';
    }

    function collect_submissions(array $offer): array {
        $items = [];
        $assessments = $offer['assessments'] ?? [];

        if (!is_array($assessments)) {
            return $items;
        }

        foreach ($assessments as $assessmentIndex => $assessment) {
            if (!is_array($assessment)) {
                continue;
            }

            $assessmentId = offer_assessment_id($assessment);
            if ($assessmentId === '') {
                continue;
            }

            $assessmentTitle = (string)($assessment['title'] ?? '');
            $submissions = $assessment['submissions'] ?? [];

            if (!is_array($submissions)) {
                continue;
            }

            foreach ($submissions as $submissionIndex => $submission) {
                if (!is_array($submission)) {
                    continue;
                }

                $group = trim((string)($submission['group'] ?? ''));
                if ($group === '') {
                    continue;
                }

                $content = $submission['content'] ?? [];
                $topic = is_array($content) ? trim((string)($content['topic'] ?? '')) : '';

                $books = [];
                if (is_array($content) && !empty($content['books']) && is_array($content['books'])) {
                    foreach ($content['books'] as $book) {
                        if (!is_array($book)) {
                            continue;
                        }

                        $books[] = [
                            'title' => trim((string)($book['title'] ?? '')),
                            'grade' => trim((string)($book['grade'] ?? '')),
                            'info'  => trim((string)($book['info'] ?? ''))
                        ];
                    }
                }

                $bookTitles = [];
                foreach ($books as $book) {
                    if ($book['title'] !== '') {
                        $bookTitles[] = $book['title'];
                    }
                }

                $items[] = [
                    'assessment_id'    => $assessmentId,
                    'assessment_title' => $assessmentTitle,
                    'assessment_index' => $assessmentIndex,
                    'submission_index' => $submissionIndex,
                    'group'            => $group,
                    'topic'            => $topic,
                    'books'            => $books,
                    'book_titles'      => $bookTitles,
                    'starred'          => !empty($submission['starred']),
                    'source'           => trim((string)($submission['source'] ?? ''))
                ];
            }
        }

        return $items;
    }

    function render_offer_card(string $id, array $offer): void {
        $label = offer_label($offer, $id);
        $professor = trim((string)($offer['professor']['name'] ?? ''));
        $classSchedule = trim((string)($offer['class_schedule'] ?? ''));
        $monitors = offer_monitors_names($offer);

        echo '<article class="offer-card" data-offer-id="' . e($id) . '"';
        echo ' data-year="' . e((string)($offer['year'] ?? '')) . '"';
        echo ' data-semester="' . e((string)($offer['semester'] ?? '')) . '">';

        echo '<h3>' . e($label) . '</h3>';

        if ($professor !== '') {
            echo '<p class="professor"><span class="label">Professor:</span> ' . e($professor) . '</p>';
        }

        if ($classSchedule !== '') {
            echo '<p class="schedule"><span class="label">Horário:</span> ' . e($classSchedule) . '</p>';
        }

        if (!empty($monitors)) {
            echo '<p class="monitors"><span class="label">Monitores:</span> ' . e(implode(', ', $monitors)) . '</p>';
        }

        echo '<a class="button" target="_self" href="' . e(build_query_url(['y' => $id, 'pg' => null])) . '">Ver detalhes</a>';
        echo '</article>';
    }

    function render_offers_list(array $offerIds): void {
        echo '<section id="offers">';
        echo '<h1>Ofertas da Disciplina</h1>';

        if (empty($offerIds)) {
            echo '<p class="fallback">Nenhuma oferta cadastrada.</p>';
            echo '</section>';
            return;
        }

        foreach ($offerIds as $id) {
            $offer = $GLOBALS['OFERTAS'][$id] ?? null;
            if (!is_array($offer)) {
                continue;
            }

            render_offer_card($id, $offer);
        }

        echo '</section>';
    }

    function render_offer_not_found(string $offerId): void {
        echo '<section id="offer">';
        echo '<h2>Oferta</h2>';
        echo '<p>A oferta solicitada não foi encontrada.</p>';
        echo '<p><a class="button" target="_self" href="./">Voltar para as ofertas</a></p>';
        echo '</section>';
    }

    function render_offer_detail(string $offerId, array $offer): void {
        $label = offer_label($offer, $offerId);
        $professor = trim((string)($offer['professor']['name'] ?? ''));
        $classSchedule = trim((string)($offer['class_schedule'] ?? ''));
        $monitors = offer_monitors_names($offer);

        echo '<section id="offer" data-offer-id="' . e($offerId) . '"';
        echo ' data-year="' . e((string)($offer['year'] ?? '')) . '"';
        echo ' data-semester="' . e((string)($offer['semester'] ?? '')) . '">';
        echo '<h1>' . e($label) . '</h1>';

        if ($professor !== '') {
            echo '<p class="professor"><span class="label">Professor:</span> ' . e($professor) . '</p>';
        }

        if ($classSchedule !== '') {
            echo '<p class="schedule"><span class="label">Horário:</span> ' . e($classSchedule) . '</p>';
        }

        if (!empty($monitors)) {
            echo '<p class="monitors"><span class="label">Monitores:</span> ' . e(implode(', ', $monitors)) . '</p>';
        }

        echo '<a class="button" target="_self" href="./">Voltar às ofertas</a>';
        echo '</section>';
    }

    function render_submission(array $item): void {
        $classes = ['submission'];
        if (!empty($item['starred'])) {
            $classes[] = 'starred';
        }

        $bookSearch = implode(' ', $item['book_titles']);
        $hasLink = !empty($item['source']);

        echo '<article class="' . e(implode(' ', $classes)) . '"';
        echo ' data-assessment="' . e($item['assessment_id']) . '"';
        echo ' data-assessment-title="' . e($item['assessment_title']) . '"';
        echo ' data-group="' . e($item['group']) . '"';
        echo ' data-starred="' . ($item['starred'] ? 'true' : 'false') . '"';
        echo ' data-topic="' . e($item['topic']) . '"';
        echo ' data-search="' . e(trim($item['assessment_id'] . ' ' . $item['assessment_title'] . ' ' . $item['group'] . ' ' . $item['topic'] . ' ' . $bookSearch)) . '">';

        // 🔗 Só cria <a> se houver source
        if ($hasLink) {
            echo '<a class="submission-link" href="' . e($item['source']) . '" target="_blank" rel="noopener noreferrer">';
        }

        echo '<h3><span class="assessment">' . e($item['assessment_id']) . '</span> Grupo ' . e($item['group']) . '</h3>';

        if (!empty($item['starred'])) {
            echo '<span class="star">★</span>';
        }

        echo '<p class="topic">' . e($item['topic']) . '</p>';

        echo '<section class="books">';
        foreach ($item['books'] as $book) {
            echo '<article class="book"';
            echo ' data-title="' . e($book['title']) . '"';
            echo ' data-grade="' . e($book['grade']) . '">';

            if ($book['title'] !== '') {
                echo '<h4 class="title">' . e($book['title']) . '</h4>';
            }

            if ($book['grade'] !== '') {
                echo '<p class="grade">' . e($book['grade']) . '</p>';
            }

            if ($book['info'] !== '') {
                echo '<p class="info">' . e($book['info']) . '</p>';
            }

            echo '</article>';
        }
        echo '</section>';

        if ($hasLink) {
            echo '</a>';
        }

        echo '</article>';
    }

    function render_gallery(string $offerId, array $offer, int $page = 1, int $perPage = 12): void {
        $items = collect_submissions($offer);
        $total = count($items);

        echo '<section id="submissions">';
        echo '<h2>Trabalhos</h2>';

        if ($total === 0) {
            echo '<p class="fallback">Nenhum trabalho cadastrado para esta oferta.</p>';
            echo '</section>';
            return;
        }

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $visible = array_slice($items, $offset, $perPage);

        echo '<section id="submission-list">';
        foreach ($visible as $item) {
            render_submission($item);
        }
        echo '</div>';

        if ($totalPages > 1) {
            echo '<nav class="pagination" aria-label="Paginação da galeria">';
            for ($n = 1; $n <= $totalPages; $n++) {
                $class = $n === $page ? ' class="current"' : '';
                echo '<a' . $class . ' href="' . e(build_query_url(['y' => $offerId, 'pg' => $n])) . '">';
                echo (string)$n;
                echo '</a>';
            }
            echo '</nav>';
        }

        echo '</section>';
    }

    $offerId = isset($_GET['y']) ? trim((string)$_GET['y']) : '';
    $page = isset($_GET['pg']) ? max(1, (int)$_GET['pg']) : 1;
    $offerIds = get_offer_ids();

    $isOfferRequest = ($offerId !== '');

    $offer = null;
    if ($isOfferRequest && offer_is_valid_id($offerId) && isset($GLOBALS['OFERTAS'][$offerId]) && is_array($GLOBALS['OFERTAS'][$offerId])) {
        $offer = $GLOBALS['OFERTAS'][$offerId];
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>MA225 - Anos Anteriores</title>

        <?php include('../assets/php/head.php'); ?>

        <link rel="preload" as="style" onload="this.remove();" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="../assets/css/pages.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <link rel="preload" as="style" onload="this.remove();" href="../assets/css/offers.css?t=<?php echo date('YmdHis'); ?>" type="text/css">
        <link rel="stylesheet" href="../assets/css/offers.css?t=<?php echo date('YmdHis'); ?>" type="text/css">

        <!-- SEO -->
        <meta name="author" content="Speck">
        <meta name="description" content="Oferecimentos Anteriores da Disciplina MA225 oferecida pelo IMECC-Unicamp">
        <meta name="keywords" content="ma225, livros didáticos, materiais didáticos, matemática, imecc, unicamp">
        <link rel="canonical" href="https://ime.unicamp.br/~ma225/ofertas/">
    </head>
    <body>
        <?php include('../assets/php/header.php'); ?>
        <main>
            <?php if (!$isOfferRequest): ?>
                <?php render_offers_list($offerIds); ?>
            <?php elseif ($offer === null): ?>
                <?php render_offer_not_found($offerId); ?>
            <?php else: ?>
                <?php render_offer_detail($offerId, $offer); ?>
                <?php render_gallery($offerId, $offer, $page, 12); ?>
            <?php endif; ?>
        </main>
        <?php include('../assets/php/footer.php'); ?>
        <script src="../assets/js/detectScroll.js"></script>
    </body>
</html>