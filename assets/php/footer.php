<?php

    include_once('parse_data.php');

    function render_professor($prof) {
        echo '<section id="professor-section">';

        if (!$prof || empty($prof['name'])) {
            echo '<h2>Professor Responsável</h2>';
            echo '<p class="fallback">Entre em contato para mais informações.</p>';
            echo '</section>';
            return;
        }

        echo '<h2 class="name"><span class="type">Prof.</span>' . e($prof['name']) . '</h2>';

        foreach ($prof as $field => $value) {
            if (in_array($field, ['name'])) continue;
            if ($value === null || $value === '') continue;

            echo '<p class="' . e($field) . '">'
                . e($value)
                . '</p>';
        }

        echo '</section>';
    }

    function render_monitors($monitors) {
        echo '<section id="monitors">';
        echo '<h2>Monitores</h2>';

        if (empty($monitors) || !is_array($monitors)) {
            echo '<p class="fallback">Sem monitores cadastrados.</p>';
            echo '</section>';
            return;
        }

        foreach ($monitors as $monitor) {
            if (empty($monitor['name'])) continue;

            echo '<article>';

            // Nome + tipo
            echo '<p class="name">';
            if (!empty($monitor['type'])) {
                echo '<span class="type">' . e($monitor['type']) . '</span>';
            }
            echo e($monitor['name']) . '</p>';

            // Demais campos dinâmicos (exceto name e type)
            foreach ($monitor as $field => $value) {
                if (in_array($field, ['name', 'type'])) continue;
                if ($value === null || $value === '') continue;

                echo '<p class="' . e($field) . '">'
                    . e($value)
                    . '</p>';
            }

            echo '</article>';
        }

        echo '</section>';
    }

?>

<footer>
    <?php render_professor(oferta('current.professor')) ?>
    <?php render_monitors(oferta('current.monitors', [])) ?>
    <section id="institucional">
        <h2 class="hidden">Institucional</h2>
        <img loading="lazy" decoding="async" sizes="(max-width: 480px) 100vw, 480px" srcset="
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_50.png 50w,
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_168.png 168w,
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_250.png 250w,
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_318.png 318w,
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_382.png 382w,
            /~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_480.png 480w"
        src="/~ma225/assets/img/logoIMECC_zovtlq_c_scale,w_480.png" alt="Logo do IMECC">
        <img loading="lazy" decoding="async" sizes="(max-width: 402px) 100vw, 402px" srcset="
            /~ma225/assets/img/logoUnicamp_dhrsm8_c_scale,w_50.png 50w,
            /~ma225/assets/img/logoUnicamp_dhrsm8_c_scale,w_164.png 164w,
            /~ma225/assets/img/logoUnicamp_dhrsm8_c_scale,w_246.png 246w,
            /~ma225/assets/img/logoUnicamp_dhrsm8_c_scale,w_402.png 402w"
        src="/~ma225/assets/img/logoUnicamp_dhrsm8_c_scale,w_402.png"
        alt="Logo da Unicamp">
    </section>
    <p class="creditos">Site desenvolvido com <span class="coracao"></span><span class="hidden">amor</span> por <a href="https://ime.unicamp.br/~speck">Speck</a></p>
</footer>