<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

global $APPLICATION, $USER;

if (!\Bitrix\Main\Loader::includeModule("iblock")) {
    ShowError("Модуль инфоблоков не подключен");
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

const CAMPAIGN_IBLOCK_ID = 10;

/**
 * @param mixed $value
 * @return array
 */
function campaignNormalizePropertyValue($value)
{
    if (is_array($value)) {
        return array_values(array_filter($value));
    }

    if (empty($value)) {
        return [];
    }

    return [$value];
}

/**
 * XML_ID варианта списка пользовательского поля UF_TYPE у раздела инфоблока.
 * В админке у значений списка должны быть заданы XML_ID: 1 — открыто, 2 — закрыто.
 *
 * @param int|string $ufTypeEnumId ID записи из b_user_field_enum (хранится в UF_TYPE раздела)
 * @return string XML_ID или пустая строка
 */
function campaignGetSectionUfTypeXmlId($ufTypeEnumId)
{
    $ufTypeEnumId = (int)$ufTypeEnumId;
    if ($ufTypeEnumId <= 0) {
        return "";
    }

    $enumIterator = CUserFieldEnum::GetList([], ["ID" => $ufTypeEnumId]);
    $enum = $enumIterator ? $enumIterator->Fetch() : null;

    return trim((string)($enum["XML_ID"] ?? ""));
}

/**
 * Закрытая кампания — только при XML_ID = 2 (открытая — XML_ID = 1).
 * Если UF_TYPE не задан или у варианта нет XML_ID — считаем открытой.
 *
 * @param string $xmlId
 * @return bool
 */
function campaignIsClosedByUfTypeXmlId($xmlId)
{
    return trim((string)$xmlId) === "2";
}

/**
 * XML_ID значения списка свойства инфоблока (кеш по ENUM_ID).
 *
 * @param int $enumId
 * @return string
 */
function campaignGetIblockPropertyEnumXmlId($enumId)
{
    static $cache = [];

    $enumId = (int)$enumId;
    if ($enumId <= 0) {
        return "";
    }

    if (array_key_exists($enumId, $cache)) {
        return $cache[$enumId];
    }

    $row = CIBlockPropertyEnum::GetByID($enumId);
    $cache[$enumId] = (is_array($row) && !empty($row["XML_ID"])) ? (string)$row["XML_ID"] : "";

    return $cache[$enumId];
}

/**
 * Тип блока кампейна: XML_ID из свойства TYPE + число 1..8 для верстки.
 *
 * @param array $fields Результат CIBlockElement::GetList / GetFields() с выбранными PROPERTY_*
 * @return array{xml_id: string, type_num: int}
 */
function campaignResolveBlockTypeFromFields(array $fields)
{
    $xmlId = trim((string)($fields["PROPERTY_TYPE_VALUE_XML_ID"] ?? $fields["~PROPERTY_TYPE_VALUE_XML_ID"] ?? ""));
    if ($xmlId === "") {
        $enumId = (int)($fields["PROPERTY_TYPE_ENUM_ID"] ?? $fields["~PROPERTY_TYPE_ENUM_ID"] ?? 0);
        $xmlId = campaignGetIblockPropertyEnumXmlId($enumId);
    }

    $typeNum = 0;
    if ($xmlId !== "") {
        if (preg_match('/(\d+)/', $xmlId, $m)) {
            $typeNum = (int)$m[1];
        }
    }

    if ($typeNum === 0) {
        $label = (string)($fields["PROPERTY_TYPE_VALUE"] ?? $fields["~PROPERTY_TYPE_VALUE"] ?? "");
        if (preg_match('/(\d+)/u', $label, $m)) {
            $typeNum = (int)$m[1];
        }
    }

    return [
        "xml_id" => $xmlId,
        "type_num" => $typeNum,
    ];
}

$campaignCode = trim((string)($_REQUEST["CAMPAIGN_CODE"] ?? ""));

if ($campaignCode === "") {
    $APPLICATION->SetTitle("Кампейны");

    $sections = [];
    $sectionRes = CIBlockSection::GetList(
        ["SORT" => "ASC", "ID" => "ASC"],
        [
            "IBLOCK_ID" => CAMPAIGN_IBLOCK_ID,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y",
        ],
        false,
        [
            "ID",
            "IBLOCK_ID",
            "NAME",
            "CODE",
            "DESCRIPTION",
            "PICTURE",
            "UF_TYPE",
        ]
    );

    while ($section = $sectionRes->GetNext()) {
        $ufTypeXmlId = campaignGetSectionUfTypeXmlId((int)$section["UF_TYPE"]);
        $isClosed = campaignIsClosedByUfTypeXmlId($ufTypeXmlId);
        $pictureSrc = "";

        if ((int)$section["PICTURE"] > 0) {
            $pictureSrc = (string)CFile::GetPath((int)$section["PICTURE"]);
        }

        $sections[] = [
            "NAME" => $section["NAME"],
            "CODE" => $section["CODE"],
            "DESCRIPTION" => (string)$section["DESCRIPTION"],
            "PICTURE_SRC" => $pictureSrc,
            "IS_CLOSED" => $isClosed,
        ];
    }
    ?>
    <div class="campaign campaign-list">
        <?php foreach ($sections as $campaign): ?>
            <a class="campaign-card" href="/campaign/<?= htmlspecialcharsbx($campaign["CODE"]) ?>/">
                <div class="campaign-card__media<?= $campaign["PICTURE_SRC"] === "" ? " campaign-card__media_no-photo" : "" ?>"
                    <?php if ($campaign["PICTURE_SRC"] !== ""): ?>
                        style="background-image: url('<?= htmlspecialcharsbx($campaign["PICTURE_SRC"]) ?>')"
                    <?php endif; ?>
                >
                    <span class="campaign-card__gradient" aria-hidden="true"></span>
                   
                    <h2 class="campaign-card__title"><?= htmlspecialcharsbx($campaign["NAME"]) ?></h2>
                </div>
                <?php if ($campaign["DESCRIPTION"] !== ""): ?>
                    <span class="campaign-card__description visually-hidden"><?= htmlspecialcharsbx($campaign["DESCRIPTION"]) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

$campaignSection = null;
$sectionRes = CIBlockSection::GetList(
    ["SORT" => "ASC", "ID" => "ASC"],
    [
        "IBLOCK_ID" => CAMPAIGN_IBLOCK_ID,
        "ACTIVE" => "Y",
        "=CODE" => $campaignCode,
    ],
    false,
    [
        "ID",
        "IBLOCK_ID",
        "NAME",
        "CODE",
        "DESCRIPTION",
        "UF_TYPE",
    ]
);

if ($section = $sectionRes->GetNext()) {
    $campaignSection = $section;
}

if (!$campaignSection) {
    \Bitrix\Iblock\Component\Tools::process404(
        "Кампейн не найден",
        true,
        true,
        true
    );
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

$ufTypeXmlId = campaignGetSectionUfTypeXmlId((int)$campaignSection["UF_TYPE"]);
$isClosedCampaign = campaignIsClosedByUfTypeXmlId($ufTypeXmlId);
$isAuthorized = $USER instanceof CUser && $USER->IsAuthorized();

$APPLICATION->SetTitle((string)$campaignSection["NAME"]);

if ($isClosedCampaign && !$isAuthorized): ?>
    <div class="campaign campaign-detail">
        <div class="campaign-closed-message">
            Этот кампейн доступен только авторизованным пользователям.
        </div>
    </div>
    <script>
        (function () {
            function campaignShowLoginModal() {
                if (!window.jQuery || !jQuery.fn.modal) {
                    return false;
                }
                var $modal = jQuery('#login-modal');
                if (!$modal.length) {
                    return false;
                }
                $modal.modal('show');
                return true;
            }
            function scheduleOpen() {
                if (!campaignShowLoginModal()) {
                    var n = 0;
                    var t = setInterval(function () {
                        if (campaignShowLoginModal() || ++n > 40) {
                            clearInterval(t);
                        }
                    }, 50);
                }
            }
            /* #login-modal и jQuery с .modal() в footer — ждём полной загрузки страницы */
            if (document.readyState === 'complete') {
                scheduleOpen();
            } else {
                window.addEventListener('load', scheduleOpen);
            }
        })();
    </script>
    <?php
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
endif;

$blocks = [];
$elementRes = CIBlockElement::GetList(
    ["SORT" => "ASC", "ID" => "ASC"],
    [
        "IBLOCK_ID" => CAMPAIGN_IBLOCK_ID,
        "ACTIVE" => "Y",
        "SECTION_ID" => (int)$campaignSection["ID"],
        "INCLUDE_SUBSECTIONS" => "N",
    ],
    false,
    false,
    ["ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT", "PROPERTY_TYPE", "PROPERTY_IMG", "PROPERTY_IMG_BIG", "PROPERTY_ITEMS"]
);

while ($fields = $elementRes->GetNext()) {
    $typeResolved = campaignResolveBlockTypeFromFields($fields);

    $blocks[] = [
        "TYPE" => (int)$typeResolved["type_num"],
        "TYPE_XML_ID" => (string)$typeResolved["xml_id"],
        "TEXT" => (string)($fields["PREVIEW_TEXT"] ?: $fields["DETAIL_TEXT"] ?: $fields["NAME"]),
        "IMG" => campaignNormalizePropertyValue($fields["PROPERTY_IMG_VALUE"] ?? []),
        "IMG_BIG" => campaignNormalizePropertyValue($fields["PROPERTY_IMG_BIG_VALUE"] ?? []),
        "ITEMS" => array_map("intval", campaignNormalizePropertyValue($fields["PROPERTY_ITEMS_VALUE"] ?? [])),
    ];
}
?>

<div class="campaign campaign-detail">
    <?php foreach ($blocks as $index => $block): ?>
        <?php
        $type = (int)$block["TYPE"];
        $imgList = $block["IMG"];
        $imgBigList = $block["IMG_BIG"];
        $text = $block["TEXT"];
        $blockNumber = $index + 1;
        ?>

        <?php if ($type === 1): ?>
            <?php $imgSrc = isset($imgBigList[0]) ? (string)CFile::GetPath((int)$imgBigList[0]) : ""; ?>
            <?php if ($imgSrc !== ""): ?>
                <div class="block1">
                    <div class="img" style="background-image: url('<?= htmlspecialcharsbx($imgSrc) ?>')"></div>
                </div>
            <?php endif; ?>

        <?php elseif ($type === 2): ?>
            <?php $imgBigSrc = isset($imgBigList[0]) ? (string)CFile::GetPath((int)$imgBigList[0]) : ""; ?>
            <div class="block2">
                <?php if ($imgBigSrc !== ""): ?>
                    <div class="img-big" style="background-image: url('<?= htmlspecialcharsbx($imgBigSrc) ?>')"></div>
                <?php endif; ?>
                <?php foreach ($imgList as $imgId): ?>
                    <?php $imgSrc = (string)CFile::GetPath((int)$imgId); ?>
                    <?php if ($imgSrc !== ""): ?>
                        <div class="img-small" style="background-image: url('<?= htmlspecialcharsbx($imgSrc) ?>')"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        <?php elseif ($type === 3): ?>
            <div class="block3">
                <?php foreach ($imgList as $imgId): ?>
                    <?php $imgSrc = (string)CFile::GetPath((int)$imgId); ?>
                    <?php if ($imgSrc !== ""): ?>
                        <img src="<?= htmlspecialcharsbx($imgSrc) ?>" alt="">
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($text !== ""): ?>
                    <div class="block-text"><?= $text ?></div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 4): ?>
            <?php
            $imgIdBlock4 = isset($imgBigList[0]) ? (int)$imgBigList[0] : (isset($imgList[0]) ? (int)$imgList[0] : 0);
            $imgSrcBlock4 = $imgIdBlock4 > 0 ? (string)CFile::GetPath($imgIdBlock4) : "";
            ?>
            <?php if ($imgSrcBlock4 !== ""): ?>
                <div class="block4">
                    <img src="<?= htmlspecialcharsbx($imgSrcBlock4) ?>" alt="">
                </div>
            <?php endif; ?>

        <?php elseif ($type === 5): ?>
            <?php if ($text !== ""): ?>
                <div class="block5">
                    <div class="block-text"><?= $text ?></div>
                </div>
            <?php endif; ?>

        <?php elseif ($type === 6): ?>
            <div class="block6">
                <?php
                $itemIds = array_values(array_filter($block["ITEMS"]));
                if (!empty($itemIds)) {
                    $filterName = "campaignItemsFilter" . $blockNumber;
                    $GLOBALS[$filterName] = ["ID" => $itemIds];

                    $APPLICATION->IncludeComponent(
                        "bitrix:catalog.section",
                        "main",
                        [
                            "IBLOCK_TYPE" => "catalog",
                            "IBLOCK_ID" => "2",
                            "FILTER_NAME" => $filterName,
                            "SHOW_ALL_WO_SECTION" => "Y",
                            "INCLUDE_SUBSECTIONS" => "Y",
                            "ELEMENT_SORT_FIELD" => "sort",
                            "ELEMENT_SORT_ORDER" => "asc",
                            "ELEMENT_SORT_FIELD2" => "id",
                            "ELEMENT_SORT_ORDER2" => "asc",
                            "PAGE_ELEMENT_COUNT" => "12",
                            "LINE_ELEMENT_COUNT" => "4",
                            "PRICE_CODE" => ["BASE"],
                            "ADD_PROPERTIES_TO_BASKET" => "Y",
                            "PARTIAL_PRODUCT_PROPERTIES" => "N",
                            "DISPLAY_TOP_PAGER" => "N",
                            "DISPLAY_BOTTOM_PAGER" => "N",
                            "SET_TITLE" => "N",
                            "SET_BROWSER_TITLE" => "N",
                            "SET_META_KEYWORDS" => "N",
                            "SET_META_DESCRIPTION" => "N",
                            "SET_STATUS_404" => "N",
                            "SHOW_404" => "N",
                            "CACHE_TYPE" => "A",
                            "CACHE_TIME" => "36000000",
                            "CACHE_GROUPS" => "Y",
                            "CACHE_FILTER" => "N",
                            "USE_MAIN_ELEMENT_SECTION" => "Y",
                            "CONVERT_CURRENCY" => "Y",
                            "CURRENCY_ID" => "RUB",
                            "SHOW_PRICE_COUNT" => "1",
                            "PRODUCT_ROW_VARIANTS" => "",
                            "PRODUCT_DISPLAY_MODE" => "Y",
                            "USE_PRODUCT_QUANTITY" => "N",
                            "ENLARGE_PRODUCT" => "PROP",
                            "ENLARGE_PROP" => "NEWPRODUCT",
                            "ADD_PICT_PROP" => "MORE_PHOTO",
                            "OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
                            "OFFER_TREE_PROPS" => ["COLOR_REF", "SIZES_SHOES", "SIZES_CLOTHES"],
                            "OFFERS_CART_PROPERTIES" => ["ARTNUMBER", "COLOR_REF", "SIZES_SHOES", "SIZES_CLOTHES"],
                            "OFFERS_FIELD_CODE" => ["", ""],
                            "OFFERS_PROPERTY_CODE" => ["COLOR_REF", "SIZES_SHOES", "SIZES_CLOTHES", ""],
                            "OFFERS_SORT_FIELD" => "sort",
                            "OFFERS_SORT_ORDER" => "asc",
                            "OFFERS_SORT_FIELD2" => "id",
                            "OFFERS_SORT_ORDER2" => "desc",
                            "OFFERS_LIMIT" => "5",
                            "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons,compare",
                            "LABEL_PROP" => ["NEWPRODUCT"],
                            "LAZY_LOAD" => "N",
                            "LOAD_ON_SCROLL" => "N",
                            "SHOW_DISCOUNT_PERCENT" => "Y",
                            "SHOW_OLD_PRICE" => "N",
                            "SHOW_SLIDER" => "Y",
                            "SLIDER_INTERVAL" => "3000",
                            "SLIDER_PROGRESS" => "N",
                            "IS_PRODUCTS_ON_MAIN" => "Y",
                        ]
                    );
                }
                ?>
            </div>

        <?php elseif ($type === 7): ?>
            <?php if (!empty($imgList)): ?>
                <div class="block7">
                    <?php foreach ($imgList as $imgId): ?>
                        <?php $imgSrc = (string)CFile::GetPath((int)$imgId); ?>
                        <?php if ($imgSrc !== ""): ?>
                            <img src="<?= htmlspecialcharsbx($imgSrc) ?>" alt="">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($type === 8): ?>
            <?php if (!empty($imgList)): ?>
                <div class="block8">
                    <?php foreach ($imgList as $imgId): ?>
                        <?php $imgSrc = (string)CFile::GetPath((int)$imgId); ?>
                        <?php if ($imgSrc !== ""): ?>
                            <img src="<?= htmlspecialcharsbx($imgSrc) ?>" alt="">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br><br>
    <?php endforeach; ?>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
