<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$isOnMain = empty($arParams['PLACEMENT']) || $arParams['PLACEMENT'] !== 'top_fixed';
?>
<?php if (!empty($arResult['ITEMS'])): ?>
    <div class="promo-stripe<?= $isOnMain ? ' promo-stripe_on-main' : '' ?>">
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
        /* Плашка над шапкой: выше по слою и не перекрывается шапкой */
        .promo-stripe-outer {
            position: relative;
            z-index: 100;
        }
        .promo-stripe {
            width: 100%;
            height: 48px;
            overflow: hidden;
            margin-top:24px;
            margin-bottom:24px;
        }
        .promo-stripe-outer .promo-stripe {
            margin-top: 0;
        }
        /* На главной — без отступов сверху и снизу */
        .promo-stripe_on-main {
            margin-top: 0;
            margin-bottom: 0;
        }
        /* Шапка под плашкой у верхнего края страницы */
        .layout__wrapper_promo-stripe-top .header {
            top: 48px;
        }
        /* После прокрутки — шапка без отступа */
        .layout__wrapper_promo-stripe-top.promo-stripe-scrolled .header {
            top: 0;
        }
        /* Контент не заходит под фиксированную шапку */
        .layout__wrapper_promo-stripe-top {
            padding-top: 108px;
        }
        .layout__wrapper_promo-stripe-top.promo-stripe-scrolled {
            padding-top: 60px;
        }
        @media (max-width: 767px) {
            .promo-stripe {
                height: 32px;
            }
            .layout__wrapper_promo-stripe-top .header {
                top: 32px;
            }
            .layout__wrapper_promo-stripe-top.promo-stripe-scrolled .header {
                top: 0;
            }
            .layout__wrapper_promo-stripe-top {
                padding-top: 71px;
            }
            .layout__wrapper_promo-stripe-top.promo-stripe-scrolled {
                padding-top: 50px;
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
            font-size: 18px;
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
    <script>
        (function() {
            var wrapper = document.querySelector('.layout__wrapper_promo-stripe-top');
            if (!wrapper) return;
            var stripeOuter = document.querySelector('.promo-stripe-outer');
            function updateScrolled() {
                var h = stripeOuter ? stripeOuter.offsetHeight : (window.innerWidth <= 767 ? 21 : 48);
                wrapper.classList.toggle('promo-stripe-scrolled', window.pageYOffset >= h);
            }
            updateScrolled();
            window.addEventListener('scroll', updateScrolled, { passive: true });
        })();
    </script>
<?php endif; ?>
