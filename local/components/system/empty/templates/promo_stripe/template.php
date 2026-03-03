<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$isFixed = ($arParams['PLACEMENT'] ?? '') === 'top_fixed';
?>
<?php if (!empty($arResult['ITEMS'])): ?>
    <div class="promo-stripe<?= $isFixed ? ' promo-stripe_fixed' : '' ?>">
        <div class="promo-stripe__slider">
            <?php foreach ($arResult['ITEMS'] as $i => $item):
            $hasLink = $item['link_url'] !== '';
            $tag = $hasLink ? 'a' : 'div';
            $hrefAttr = $hasLink ? ' href="' . htmlspecialchars($item['link_url']) . '"' : '';
            ?>
            <<?= $tag ?> class="promo-stripe__slide<?= $i === 0 ? ' promo-stripe__slide_active' : '' ?><?= $hasLink ? ' promo-stripe__slide_clickable' : '' ?>"
            style="background-color:#<?= htmlspecialchars($item['color_fon']) ?>; color:#<?= htmlspecialchars($item['color_text']) ?>;"
            data-index="<?= $i ?>"<?= $hrefAttr ?>>
            <div class="promo-stripe__inner">
                <?php if ($item['text'] !== ''): ?>
                    <span class="promo-stripe__text"><?= htmlspecialchars($item['text']) ?></span>
                    <?php if ($item['link_text'] !== ''): ?>
                        <span class="promo-stripe__sep">. </span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($item['link_text'] !== ''): ?>
                    <span class="promo-stripe__text"><?= htmlspecialchars($item['link_text']) ?></span>
                <?php endif; ?>
            </div>
        </<?= $tag ?>>
        <?php endforeach; ?>
    </div>
    </div>
    <style>
        .promo-stripe {
            width: 100%;
            height: 48px;
            overflow: hidden;
            margin-top:24px;
            margin-bottom:24px;
        }
        .promo-stripe_fixed {
            margin: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        /* Отступ: плашка + высота шапки, чтобы контент не заходил под фиксированную шапку */
        .layout__wrapper_promo-stripe-top {
            padding-top: 108px; /* 48px плашка + ~60px шапка */
        }
        /* Шапка под плашкой, не перекрывается при фиксации */
        .layout__wrapper_promo-stripe-top .header {
            top: 48px;
        }
        @media (max-width: 767px) {
            .promo-stripe {
                height: 21px;
            }
            .promo-stripe_fixed {
                margin: 0;
            }
            .layout__wrapper_promo-stripe-top {
                padding-top: 71px; /* 21px плашка + ~50px шапка */
            }
            .layout__wrapper_promo-stripe-top .header {
                top: 21px;
            }
        }
        .promo-stripe__slider {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .promo-stripe__slide {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .promo-stripe__slide_active {
            opacity: 1;
            visibility: visible;
            position: relative;
        }
        .promo-stripe__inner {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.15em;
            padding: 0 12px;
            font-size: 16px;
            line-height: 1.2;
            text-align: center;
        }
        @media (max-width: 767px) {
            .promo-stripe__inner {
                font-size: 12px;
                padding: 0 8px;
            }
        }
        .promo-stripe__sep {
            flex-shrink: 0;
        }
        .promo-stripe__slide_clickable {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }
        .promo-stripe__slide_clickable .promo-stripe__link {
            text-decoration: underline;
        }
        .promo-stripe__slide_clickable:hover .promo-stripe__link {
            text-decoration: none;
        }
    </style>
    <?php if (count($arResult['ITEMS']) > 1): ?>
        <script>
            (function() {
                var slides = document.querySelectorAll('.promo-stripe__slide');
                if (slides.length < 2) return;
                var idx = 0;
                setInterval(function() {
                    slides[idx].classList.remove('promo-stripe__slide_active');
                    idx = (idx + 1) % slides.length;
                    slides[idx].classList.add('promo-stripe__slide_active');
                }, 5000);
            })();
        </script>
    <?php endif; ?>
<?php endif; ?>
