<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Level44\Base;

if (empty(Base::$typePage)) {
    Base::$typePage = "layout";
}

switch (Base::$typePage) {
    case 'home':
        $APPLICATION->AddViewContent("header-class", 'transparent');
        break;
    case 'product':
        $APPLICATION->AddViewContent("header-class", 'product');
        break;
    default:
        $APPLICATION->AddViewContent("header-class", 'navbar-light');
        break;
}

$APPLICATION->AddViewContent("type-page", Base::$typePage);
?>
</div>
<?php $APPLICATION->ShowViewContent("catalog-filters"); ?>
<? $APPLICATION->ShowViewContent("preorder-form"); ?>
<? $APPLICATION->ShowViewContent("sizes-table"); ?>

<footer class="footer">
    <div class="content-container">
        <div class="footer__subscribe footer__col">
            <? /*<div class="email">
                <p class="email__text">Подпишитесь на нашу e-mail рассылку, чтобы первыми получать информацию о новинках
                    и спецпредложениях</p>
                <a class="btn btn-dark" href="#">Подписаться</a>
            </div>*/ ?>

            <div class="news">
                <h5 class="footer__title"><?= Loc::getMessage('FOOTER_FOLLOW') ?></h5>
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
        </div>
        <div class="footer__address footer__col">
            <h5 class="footer__title"><?= Loc::getMessage('FOOTER_SHOPS') ?></h5>
            <? $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => SITE_DIR . "include/footer_flagman.php",
                ),
                false
            ); ?>
            <? $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => SITE_DIR . "include/footer_showroom.php",
                ),
                false
            ); ?>
        </div>
        <div class="footer__info footer__col">
            <h5 class="footer__title"><?= Loc::getMessage("FOOTER_BUYERS") ?></h5>
            <? $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "footer_links",
                Array(
                    "ROOT_MENU_TYPE" => "to_customers",
                    "MAX_LEVEL" => "1",
                    "CHILD_MENU_TYPE" => "top",
                    "USE_EXT" => "N",
                    "DELAY" => "N",
                    "ALLOW_MULTI_SELECT" => "N",
                    "MENU_CACHE_TYPE" => "N",
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "MENU_CACHE_GET_VARS" => ""
                )
            ); ?>
        </div>
        <div class="footer__logo footer__col">
            <a href="<?= SITE_DIR ?>">LEVEL44</a>
        </div>
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.site.selector",
            "mobile",
            [
                "SITE_LIST"  => ["*all*"],
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600",
            ]
        ); ?>
    </div>
</footer>
<? $APPLICATION->ShowViewContent("image-modal"); ?>
</div>

<div class="cookie-modal" id="cookie-modal">
    <div class="cookie-modal__content">
        <div class="message"><?= Loc::getMessage('COOKIE_MODAL_DESCRIPTION', ['#SITE_DIR#' => SITE_DIR]) ?></div>
        <div class="action">
            <button class="btn btn-dark btn-block js-cookie-accept" id="cookie-accept"
                    type="button"><?= Loc::getMessage('COOKIE_MODAL_BUTTON_TEXT') ?>
            </button>
        </div>
    </div>
</div>

<div class="modal" id="login-modal" tabindex="-1" aria-hidden="true" data-backurl="<?= SITE_DIR ?>personal/">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="title"><?= Loc::getMessage('LOGIN_MODAL_TITLE') ?></div>
            <button class="btn btn-link btn-close" type="button" data-bs-dismiss="modal" aria-label="Close">
                <svg class="icon icon-close-small btn-close__icon">
                    <use xlink:href="#close-small"></use>
                </svg>
            </button>
            <?
           $APPLICATION->IncludeComponent(
                "bxmaker:authuserphone.simple",
                ".default",
                array(
                    //параметры вызова для переопределения поведения
                ),
                false
            );
            ?>
            <div style="padding-left:32px; padding-right:32px;">
            <?
            $APPLICATION->IncludeComponent(
                'bxmaker:authid.area',
                '',
                [
                    'SHOW_LINE' => 'Y'
                ]
            )
            ?>
            </div>

        </div>
    </div>
</div>

<?
Base::loadScripts();
?>
<div class="d-none">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <symbol viewBox="0 0 24 24" id="arrow-back" xmlns="http://www.w3.org/2000/svg">
            <path d="M13 2L4 12l9 10M4 12h17" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 16 16" id="arrow-down" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 4.822l6 6.356 6-6.329" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="arrow-left" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 1L8 12l8 11" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="arrow-right" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 23l8-11L8 1" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="basket" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.698 8.8a.775.775 0 01.77-.692h13.064c.396 0 .728.298.77.692l1.37 12.742a.775.775 0 01-.77.857H4.098c-.46 0-.82-.4-.77-.857L4.698 8.8zM8.2 5.373a3.773 3.773 0 017.545 0v2.635H8.2V5.373z"
                  data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="burger" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 3.4h24m-24 8h24m-24 8h24" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 25" id="close" xmlns="http://www.w3.org/2000/svg">
            <path d="M3.473 20.914L20.444 3.943m.083 16.971L3.557 3.944" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 16 16" id="close-small" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3l10 10m0-10L3 13" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="favorites" xmlns="http://www.w3.org/2000/svg">
            <path opacity=".4" d="M4 2.5h16v19l-8-4.75-8 4.75v-19z" data-fill="true"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="favorites-add" xmlns="http://www.w3.org/2000/svg">
            <path d="M19.4 3.1v17.346l-7.093-4.212-.307-.182-.307.182L4.6 20.446V3.1h14.8z" data-stroke="true"
                  fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="filters" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 6.9l13.5-.002M24 6.9h-4.5M0 16.9l4.5.001m19.5 0H10.5" data-stroke="true" fill="none"/>
            <circle cx="7.5" cy="16.9" r="2.9" data-stroke="true" fill="none"/>
            <circle cx="16.5" cy="6.9" r="2.9" data-stroke="true" fill="none"/>
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
        <symbol viewBox="0 0 24 24" id="play" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 4.747v14.506a.4.4 0 00.622.332L17.5 12.333a.4.4 0 000-.666L6.62 4.415a.4.4 0 00-.62.332z"
                  data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 25" id="profile" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="7.5" r="5.4" data-stroke="true" fill="none"/>
            <path d="M12 15.1c1.087 0 2.164.169 3.184.497l.433.15a9.61 9.61 0 012.719 1.549l.334.286a8.467 8.467 0 011.839 2.349l.184.376c.382.828.609 1.704.68 2.593H2.627c.06-.753.231-1.496.515-2.21l.165-.383a8.335 8.335 0 011.706-2.424l.317-.301a9.474 9.474 0 012.628-1.665l.425-.17a10.343 10.343 0 013.152-.637L12 15.1z"
                  data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 24 24" id="search" xmlns="http://www.w3.org/2000/svg">
            <circle cx="10.636" cy="10.136" r="8.536" data-stroke="true" fill="none"/>
            <path d="M16.368 16.238l6.338 6.337" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 14 14" id="stop" xmlns="http://www.w3.org/2000/svg">
            <circle cx="7" cy="7" r="6.5" data-stroke="true" fill="none"/>
            <path d="M1.98 11.78l9.334-9.334" data-stroke="true" fill="none"/>
        </symbol>
        <symbol viewBox="0 0 14 14" id="whatsapp" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#qa)" fill="none">
                <path d="M.775 13.292l.334-1.222.101-.363c.077-.274.157-.56.226-.84v-.002a.952.952 0 00-.088-.663C.767 9.172.46 8.085.503 6.938c.104-2.68 1.327-4.645 3.73-5.798 3.794-1.819 8.27.416 9.121 4.57.73 3.56-1.61 7.032-5.166 7.677a6.424 6.424 0 01-4.264-.66h-.001a.987.987 0 00-.677-.08 272.999 272.999 0 00-2.472.645z"
                      data-stroke="true" fill="none"/>
                <path d="M8.8 10.266c1.048-.017 1.656-.548 1.736-1.416.014-.15-.001-.284-.157-.359-.465-.222-.927-.448-1.393-.664-.157-.073-.29-.04-.401.115-.154.214-.331.41-.495.618-.104.132-.23.15-.377.09a4.74 4.74 0 01-2.36-2.026c-.09-.15-.067-.269.046-.4a3.82 3.82 0 00.412-.556.428.428 0 00.014-.325 25.374 25.374 0 00-.539-1.3.585.585 0 00-.222-.254c-.214-.123-.737-.046-.915.124-.528.503-.738 1.117-.615 1.84.1.587.405 1.076.743 1.553.741 1.044 1.6 1.956 2.807 2.467.585.247 1.181.47 1.715.493z"
                      data-fill="true"/>
            </g>
            <defs fill="none">
                <clipPath id="qa" fill="none"/>
            </defs>
        </symbol>
    </svg>
</div>


</body>
</html>
