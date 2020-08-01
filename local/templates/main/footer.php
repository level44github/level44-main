<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

if (empty(\Level44\Base::$typePage)) {
    \Level44\Base::$typePage = "layout";
}

$APPLICATION->AddViewContent("type-page", \Level44\Base::$typePage);
?>
</div>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-6 col-lg-3 order-3 order-lg-1 footer__divider">
                <div class="mb-2">© <?= date("Y") ?> Level 44</div>
                <div>
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => SITE_DIR . "include/footer_phone.php",
                        ),
                        false
                    ); ?>
                </div>
                <div><?= Loc::getMessage("CREATE_IN") ?><a class="footer__link"
                                           href="https://genue.ru"
                                           target="_blank">Genue</a>
                </div>
            </div>
            <div class="col-6 col-lg-3 order-2">
                <div class="footer__title"><?= Loc::getMessage("FOOTER_BUYERS") ?></div>
                <? $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "footer_links",
                    Array(
                        "ROOT_MENU_TYPE" => "to_customers",
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "top",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "Y",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => ""
                    )
                ); ?>
            </div>
            <div class="col-6 col-lg-3 order-2">
                <div class="footer__title footer__title_level">LEVEL44</div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="footer__link pb-0">Showroom</a></li>
                    <li class="nav-item text-muted pb-2">
                        <? $APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => SITE_DIR . "include/footer_showroom.php",
                            ),
                            false
                        ); ?>
                    </li>
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "footer_links",
                        Array(
                            "ROOT_MENU_TYPE" => "links",
                            "MAX_LEVEL" => "1",
                            "CHILD_MENU_TYPE" => "top",
                            "USE_EXT" => "N",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "Y",
                            "MENU_CACHE_TYPE" => "N",
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => "",
                            "EXT_LINKS" => "Y",
                        )
                    ); ?>
            </div>
            <div class="col-6 col-lg-2 order-4 order-lg-4 footer__divider">
                <img class="img-fluid mr-3" src="<?= \Level44\Base::getAssetsPath() ?>/img/visa.svg" alt="">
                <img class="img-fluid" src="<?= \Level44\Base::getAssetsPath() ?>/img/master-card.svg" alt="">
            </div>
        </div>
    </div>
</footer>
</div>
<?
\Level44\Base::loadScripts();
?>
<div class="d-none">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <symbol viewBox="0 0 14 14" id="arrow-left" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 7h13m-7 6L1 7l6-6" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 13 16" id="basket" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.726 5.68a.5.5 0 01.497-.447h8.501a.5.5 0 01.497.447l.997 9.267a.5.5 0 01-.498.553H1.227a.5.5 0 01-.498-.553l.997-9.267zm2.067-2.5a2.68 2.68 0 115.36 0v2.014h-5.36V3.18z"
                  data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 10 9" id="close" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.146 8.646l8-8m.208 8l-8-8" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 14 14" id="instagram" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M4.158 12.996a934.657 934.657 0 005.06-.001l.6-.001h.005l.064-.003.214-.014c.186-.013.347-.03.422-.046 1.472-.301 2.474-1.426 2.474-3.043V7.026l.001-.835c.002-.702.003-1.397-.005-2.094a3.065 3.065 0 00-.066-.633c-.335-1.49-1.543-2.458-3.094-2.461a1256.78 1256.78 0 00-5.663.001h-.002c-.176 0-.344.01-.504.03-1.505.203-2.658 1.518-2.661 3.03-.004 1.96-.005 3.916.003 5.873.001.205.024.412.068.605.338 1.485 1.542 2.45 3.084 2.454zM.006 9.942c-.008-1.96-.007-3.92-.003-5.88C.007 2.05 1.533.313 3.533.045c.21-.028.423-.04.635-.04C6.058 0 7.946-.002 9.835.003c2.012.004 3.629 1.29 4.068 3.24.062.274.087.561.09.843.008.704.007 1.409.005 2.113v3.689c0 2.112-1.356 3.63-3.274 4.023-.292.06-.883.083-.883.083s-3.79.008-5.686.002C2.152 13.99.541 12.708.1 10.765a3.82 3.82 0 01-.094-.823z"
                  data-fill="true"/>
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M6.995 5.17a1.825 1.825 0 100 3.65 1.825 1.825 0 000-3.65zM4.17 6.995a2.825 2.825 0 115.65 0 2.825 2.825 0 01-5.65 0z"
                  data-fill="true"/>
            <circle cx="10.5" cy="3.5" r=".875" data-fill="true"/>
        </symbol>
        <symbol viewBox="0 0 16 16" id="search" xmlns="http://www.w3.org/2000/svg">
            <circle cx="6.645" cy="6.645" r="6.145" data-stroke="true" fill="none"/>
            <path d="M10.857 11.037l4.609 4.609" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 14 14" id="stop" xmlns="http://www.w3.org/2000/svg">
            <circle cx="7" cy="7" r="6.5" data-stroke="true" fill="none"/>
            <path d="M1.98 11.78l9.334-9.334" data-stroke="true" fill="none"/>
        </symbol>
        <symbol width="14" height="16" viewBox="0 0 14 16" xmlns="http://www.w3.org/2000/svg">
          <path d="M1.5791 12.624H8.46289C8.4082 13.5469 7.82031 14.1348 6.99316 14.1348C6.17285 14.1348 5.57812 13.5469 5.53027 12.624H4.46387C4.51855 13.9365 5.55078 15.0918 6.99316 15.0918C8.44238 15.0918 9.47461 13.9434 9.5293 12.624H12.4141C13.0566 12.624 13.4463 12.2891 13.4463 11.7969C13.4463 11.1133 12.749 10.498 12.1611 9.88965C11.71 9.41797 11.5869 8.44727 11.5322 7.66113C11.4844 4.96777 10.7871 3.22461 8.96875 2.56836C8.73633 1.67969 8.00488 0.96875 6.99316 0.96875C5.98828 0.96875 5.25 1.67969 5.02441 2.56836C3.20605 3.22461 2.50879 4.96777 2.46094 7.66113C2.40625 8.44727 2.2832 9.41797 1.83203 9.88965C1.2373 10.498 0.546875 11.1133 0.546875 11.7969C0.546875 12.2891 0.929688 12.624 1.5791 12.624ZM1.87305 11.5918V11.5098C1.99609 11.3047 2.40625 10.9082 2.76172 10.5049C3.25391 9.95801 3.48633 9.08301 3.54785 7.74316C3.60254 4.7627 4.49121 3.80566 5.66016 3.49121C5.83105 3.4502 5.92676 3.36133 5.93359 3.19043C5.9541 2.47266 6.36426 1.97363 6.99316 1.97363C7.62891 1.97363 8.03223 2.47266 8.05957 3.19043C8.06641 3.36133 8.15527 3.4502 8.32617 3.49121C9.50195 3.80566 10.3906 4.7627 10.4453 7.74316C10.5068 9.08301 10.7393 9.95801 11.2246 10.5049C11.5869 10.9082 11.9902 11.3047 12.1133 11.5098V11.5918H1.87305Z" fill="#212121"/>
        </symbol>

        <symbol viewBox="0 0 14 14" id="whatsapp" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#gclip0)" fill="none">
                <path d="M.775 13.292l.334-1.222.101-.363c.077-.274.157-.56.226-.84v-.002a.952.952 0 00-.088-.663C.767 9.172.46 8.085.503 6.938c.104-2.68 1.327-4.645 3.73-5.798 3.794-1.819 8.27.416 9.121 4.57.73 3.56-1.61 7.032-5.166 7.677a6.424 6.424 0 01-4.264-.66h-.001a.987.987 0 00-.677-.08 272.999 272.999 0 00-2.472.645z"
                      data-stroke="true" fill="none"/>
                <path d="M8.8 10.266c1.048-.017 1.656-.548 1.736-1.416.014-.15-.001-.284-.157-.359-.465-.222-.927-.448-1.393-.664-.157-.073-.29-.04-.401.115-.154.214-.331.41-.495.618-.104.132-.23.15-.377.09a4.74 4.74 0 01-2.36-2.026c-.09-.15-.067-.269.046-.4a3.82 3.82 0 00.412-.556.428.428 0 00.014-.325 25.374 25.374 0 00-.539-1.3.585.585 0 00-.222-.254c-.214-.123-.737-.046-.915.124-.528.503-.738 1.117-.615 1.84.1.587.405 1.076.743 1.553.741 1.044 1.6 1.956 2.807 2.467.585.247 1.181.47 1.715.493z"
                      data-fill="true"/>
            </g>
            <defs fill="none">
                <clipPath id="gclip0" fill="none"/>
            </defs>
        </symbol>
    </svg>
</div>


</body>
</html>
