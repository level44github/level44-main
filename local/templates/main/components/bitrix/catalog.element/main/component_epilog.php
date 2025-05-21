<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Level44\PreOrder;
/**
 * @var array $templateData
 * @var array $arParams
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

global $APPLICATION;

if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateFolder.'/themes/'.$templateData['TEMPLATE_THEME'].'/style.css');
	$APPLICATION->SetAdditionalCSS('/bitrix/css/main/themes/'.$templateData['TEMPLATE_THEME'].'/style.css', true);
}

if (!empty($templateData['TEMPLATE_LIBRARY']))
{
	$loadCurrency = false;

	if (!empty($templateData['CURRENCIES']))
	{
		$loadCurrency = Loader::includeModule('currency');
	}

	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency)
	{
		?>
		<script>
			BX.Currency.setCurrencies(<?=$templateData['CURRENCIES']?>);
		</script>
		<?
	}
}

if (isset($templateData['JS_OBJ']))
{
	?>
	<script>
		BX.ready(BX.defer(function(){
			if (!!window.<?=$templateData['JS_OBJ']?>)
			{
				window.<?=$templateData['JS_OBJ']?>.allowViewedCount(true);
			}
		}));
	</script>

	<?
	// check compared state
	if ($arParams['DISPLAY_COMPARE'])
	{
		$compared = false;
		$comparedIds = array();
		$item = $templateData['ITEM'];

		if (!empty($_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]))
		{
			if (!empty($item['JS_OFFERS']))
			{
				foreach ($item['JS_OFFERS'] as $key => $offer)
				{
					if (array_key_exists($offer['ID'], $_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]['ITEMS']))
					{
						if ($key == $item['OFFERS_SELECTED'])
						{
							$compared = true;
						}

						$comparedIds[] = $offer['ID'];
					}
				}
			}
			elseif (array_key_exists($item['ID'], $_SESSION[$arParams['COMPARE_NAME']][$item['IBLOCK_ID']]['ITEMS']))
			{
				$compared = true;
			}
		}

		if ($templateData['JS_OBJ'])
		{
			?>
			<script>
				BX.ready(BX.defer(function(){
					if (!!window.<?=$templateData['JS_OBJ']?>)
					{
						window.<?=$templateData['JS_OBJ']?>.setCompared('<?=$compared?>');

						<? if (!empty($comparedIds)): ?>
						window.<?=$templateData['JS_OBJ']?>.setCompareInfo(<?=CUtil::PhpToJSObject($comparedIds, false, true)?>);
						<? endif ?>
					}
				}));
			</script>
			<?
		}
	}

	// select target offer
	$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	$offerNum = false;
	$offerId = (int)$this->request->get('OFFER_ID');
	$offerCode = $this->request->get('OFFER_CODE');

	if ($offerId > 0 && !empty($templateData['OFFER_IDS']) && is_array($templateData['OFFER_IDS']))
	{
		$offerNum = array_search($offerId, $templateData['OFFER_IDS']);
	}
	elseif (!empty($offerCode) && !empty($templateData['OFFER_CODES']) && is_array($templateData['OFFER_CODES']))
	{
		$offerNum = array_search($offerCode, $templateData['OFFER_CODES']);
	}

	if (!empty($offerNum))
	{
		?>
		<script>
			BX.ready(function(){
				if (!!window.<?=$templateData['JS_OBJ']?>)
				{
					window.<?=$templateData['JS_OBJ']?>.setOffer(<?=$offerNum?>);
				}
			});
		</script>
		<?
	}

    $preOrder = new PreOrder($arResult["ACTUAL_ITEM"]["ID"]);
    ?>
    <script>
        BX.ready(function () {
            if (!!window.<?=$templateData['JS_OBJ']?>) {
                window.<?=$templateData['JS_OBJ']?>.setPreOrderData(<?=$preOrder->getInitJSData()?>);
            }
        });
    </script>
    <?
}?>

<? if (!empty($arResult["ACTUAL_ITEM"]['MORE_PHOTO'])): ?>
    <? ob_start(); ?>
    <div class="modal fade" id="product-image-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button class="close modal-close" type="button" aria-label="Закрыть">
                    <svg class="icon icon-close ">
                        <use xlink:href="#close"></use>
                    </svg>
                </button>
                <div class="modal-body p-0">
                    <div class="embla" data-mouse-scroll="false" data-loop="true" data-autoplay="false">
                        <div class="embla__container">
                            <? foreach ($arResult["ACTUAL_ITEM"]['MORE_PHOTO'] as $index => $item): ?>
                                <? if ($item['IS_VIDEO']): ?>
                                    <div class="embla__slide">
                                        <div class="embla__slide-content">
                                            <div class="product__video-wrapper">
                                                <div class="video-image">
                                                    <img class="img-fluid" src="<?= $item['POSTER_SRC'] ?>"
                                                         alt="">
                                                    <svg class="icon icon-play video-play-icon">
                                                        <use xlink:href="#play"></use>
                                                    </svg>
                                                </div>
                                                <video class="product__video" loop playsinline>
                                                    <source src="<?= $item['SRC'] ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        </div>
                                    </div>
                                <? else: ?>
                                    <div class="embla__slide">
                                        <div class="embla__slide-content">
                                            <img class="img-fluid" src="<?= $item['SRC'] ?>" alt="">
                                        </div>
                                    </div>
                                <? endif; ?>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? $APPLICATION->AddViewContent("image-modal", ob_get_clean()); ?>
<? endif; ?>

<? ob_start(); ?>
    <div class="js-subscribe-modal modal fade" id="subscribe-modal" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="js-subscribe-form modal-dialog modal-dialog-centered product-subscribe__dialog"
             role="document"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                    <button class="close modal-close" type="button"
                            aria-label="<?= Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE") ?>">
                        <svg class="icon icon-close ">
                            <use xlink:href="#close"></use>
                        </svg>
                    </button>
                </div>
                <div class="js-subscribe-form-body modal-body product-subscribe__body">
                    <div class="mb-3"><?= Loc::getMessage("DESC_POPUP_SUBSCRIBED") ?></div>
                    <form id="bx-catalog-subscribe-form">
                        <input type="hidden" class="js-preorder-productId" name="productId"
                               value="<?= $arResult["ACTUAL_ITEM"]["ID"] ?>">
                        <input type="hidden" name="siteId" value="<?= SITE_ID ?>">
                        <div id="bx-catalog-subscribe-form-div" class="form-group">
                            <div class="js-errors" style="color: red;"></div>
                            <label for="subscribe-email" class="sr-only">E-mail</label>
                            <input id="subscribe-email" class="form-control" type="text"
                                   name="email" placeholder="E-mail">
                            <p></p>
                            <label for="subscribe-tel"
                                   class="sr-only"><?= Loc::getMessage("PHONE_POPUP_SUBSCRIBED") ?></label>
                            <input id="subscribe-tel" class="form-control" type="text"
                                   name="phone"
                                   placeholder="<?= Loc::getMessage("PHONE_POPUP_SUBSCRIBED") ?>">
                        </div>
                        <button type="submit" class="js-subscribe-button btn btn-dark btn-block">
                            <?= Loc::getMessage("BTN_SEND_POPUP_SUBSCRIBED") ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="js-subscribe-suc modal-dialog modal-dialog-centered product-subscribe__dialog"
             style="display: none"
             role="document"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= Loc::getMessage("TITLE_POPUP_SUBSCRIBED") ?></h5>
                    <button class="close modal-close" type="button"
                            aria-label="<?= Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE") ?>">
                        <svg class="icon icon-close ">
                            <use xlink:href="#close"></use>
                        </svg>
                    </button>
                </div>
                <div class="modal-body product-subscribe__body">
                    <div class="js-text px-lg-1 mb-3"><?= Loc::getMessage("SUCCESS_POPUP_SUBSCRIBED") ?></div>
                    <button class="btn btn-dark btn-block modal-close" data-dismiss="modal"
                            aria-label="<?= Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE") ?>"
                            type="submit">
                        <?= Loc::getMessage("CPST_SUBSCRIBE_BUTTON_CLOSE") ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
<? $APPLICATION->AddViewContent("preorder-form", ob_get_clean()); ?>

<? ob_start(); ?>
<div class="modal fade" id="dimension__table-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= Loc::getMessage("SIZE_TABLE") ?></h5>
                <button class="close modal-close" type="button" aria-label="Закрыть">
                    <svg class="icon icon-close ">
                        <use xlink:href="#close"></use>
                    </svg>
                </button>
            </div>
            <? if ($arResult["IS_SHOES"]): ?>
                <div class="modal-body px-0">
                    <div class="d-none d-lg-block">
                        <table class="table table-hover table-borderless">
                            <thead>
                            <tr>
                                <th><?= Loc::getMessage("SIZE_TABLE_SIZE") ?></th>
                                <th><?= Loc::getMessage("SIZE_TABLE_FOOT_LENGTH") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>37</td>
                                <td>23</td>
                            </tr>
                            <tr>
                                <td>38</td>
                                <td>24</td>
                            </tr>
                            <tr>
                                <td>39</td>
                                <td>25</td>
                            </tr>
                            <tr>
                                <td>40</td>
                                <td>26</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-lg-none">
                        <table class="table table-borderless">
                            <thead>
                            <tr>
                                <td>37</td>
                                <td>38</td>
                                <td>39</td>
                                <td>40</td>
                            </tr>
                            </thead>
                            <thead>
                            <tr>
                                <th colspan="4"><?= Loc::getMessage("SIZE_TABLE_FOOT_LENGTH") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>23</td>
                                <td>24</td>
                                <td>25</td>
                                <td>25</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <? else: ?>
                <div class="modal-body px-0">
                    <div class="d-none d-lg-block">
                        <table class="table table-hover table-borderless">
                            <thead>
                            <tr>
                                <th><?= Loc::getMessage("SIZE_TABLE_SIZE") ?></th>
                                <th><?= Loc::getMessage("CHEST_CIRCUMFERENCE") ?></th>
                                <th><?= Loc::getMessage("HOIST_GIRTH") ?></th>
                                <th><?= Loc::getMessage("HIP_GIRTH") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>XXS</td>
                                <td>80</td>
                                <td>60</td>
                                <td>88</td>
                            </tr>
                            <tr>
                                <td>XS</td>
                                <td>84</td>
                                <td>64</td>
                                <td>92</td>
                            </tr>
                            <tr>
                                <td>S</td>
                                <td>88</td>
                                <td>68</td>
                                <td>96</td>
                            </tr>
                            <tr>
                                <td>M</td>
                                <td>92</td>
                                <td>72</td>
                                <td>100</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-lg-none">
                        <table class="table table-borderless">
                            <thead>
                            <tr>
                                <td>XXS</td>
                                <td>XS</td>
                                <td>S</td>
                                <td>M</td>
                            </tr>
                            </thead>
                            <thead>
                            <tr>
                                <th colspan="4"><?= Loc::getMessage("CHEST_CIRCUMFERENCE") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>80</td>
                                <td>84</td>
                                <td>88</td>
                                <td>92</td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th colspan="4"><?= Loc::getMessage("HOIST_GIRTH") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>60</td>
                                <td>64</td>
                                <td>68</td>
                                <td>72</td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th colspan="4"><?= Loc::getMessage("HIP_GIRTH") ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>88</td>
                                <td>92</td>
                                <td>96</td>
                                <td>100</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <? endif; ?>
        </div>
    </div>
</div>
<? $APPLICATION->AddViewContent("sizes-table", ob_get_clean()); ?>
