<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php if (!empty($arResult['SLIDES'])): ?>
    <div class="home">
        <div class="embla" data-mouse-scroll="false" data-autoplay="true">
            <div class="embla__container">
                <? foreach ($arResult['SLIDES'] as $slide): ?>
                    <div class="embla__slide">
                        <a class="embla__slide-link"
                            <? if (!empty($slide['link']['address'])): ?>
                                href="<?= $slide['link']['address'] ?>"
                            <? endif; ?>
                        >
                            <div class="embla__slide-content">
                                <? if (!empty($desktopSingle = $slide['files']['desktop']['single'])): ?>
                                    <? if ($desktopSingle['isVideo']): ?>
                                        <video class="banner-video desktop" autoplay muted loop playsinline>
                                            <source src="<?= $desktopSingle['src'] ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <? else: ?>
                                        <img class="banner desktop" src="<?= $desktopSingle['src'] ?>"
                                             alt="<?= $slide['title'] ?>">
                                    <? endif; ?>
                                <? elseif (!empty($desktopSplit = $slide['files']['desktop']['split'])): ?>
                                    <div class="embla__slide-double-images desktop">
                                        <? foreach ($desktopSplit as $key => $part): ?>
                                            <img src="<?= $part['src'] ?>" alt="<?= $slide['title'] ?>">
                                        <? endforeach; ?>
                                    </div>
                                <? endif; ?>

                                <? if ($slide['files']['mobile']['isVideo']): ?>
                                    <video class="banner-video mobile" autoplay muted loop playsinline>
                                        <source src="<?= $slide['files']['mobile']['src'] ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <? else: ?>
                                    <img class="banner mobile" src="<?= $slide['files']['mobile']['src'] ?>"
                                         alt="<?= $slide['title'] ?>">
                                <? endif; ?>

                                <div class="slide-info">
                                    <? if (!empty($slide['text'])): ?>
                                        <h4 class="slide-uptitle"><?= $slide['text'] ?></h4>
                                    <? endif; ?>
                                    <? if (!empty($slide['title'])): ?>
                                        <h2 class="slide-title"><?= $slide['title'] ?></h2>
                                    <? endif; ?>
                                    <? if (!empty($slide['link']['text'])): ?>
                                        <button class="btn btn-text slide-btn"><?= $slide['link']['text'] ?></button>
                                    <? endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <? endforeach; ?>
            </div>
            <button class="btn btn-link embla__arrow prev" type="button" aria-label="Arrow prev">
                <svg class="icon icon-arrow-left embla__arrow__icon">
                    <use xlink:href="#arrow-left"></use>
                </svg>
            </button>
            <button class="btn btn-link embla__arrow next" type="button" aria-label="Arrow next">
                <svg class="icon icon-arrow-right embla__arrow__icon">
                    <use xlink:href="#arrow-right"></use>
                </svg>
            </button>
            <div class="embla__dots">
                <? foreach ($arResult['SLIDES'] as $index => $slide): ?>
                    <button type="button" data-index="<?= $index ?>">
                        <div class="button-body"></div>
                    </button>
                <? endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>