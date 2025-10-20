<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */

/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect($arResult['LINK_TO_LOGIN']);
}

if ($arParams["MAIN_CHAIN_NAME"] !== '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem('Система привелегий');

$APPLICATION->AddViewContent("personal.back-link", $arResult['SEF_FOLDER']);
$APPLICATION->AddViewContent("personal.navigation-title", 'Система привелегий');

$score=\Acrit\Bonus\Core::getUserBalanceFormat($USER->GetID(), $accountId = false);

$orderSum=getUserOrderSumm($USER->GetID());

$delta=300000-$orderSum;

$deltaLine=($orderSum/300000)*100;

$bonusHistory=\Acrit\Bonus\Core::getUserTransactions($USER->GetID());


$userInfo = CUser::GetByID($USER->GetID())->fetch();


?>
<div class="profile__title">Система привелегий</div>
<div class="profile profile-orders">
    <div class="loyalty-plashka">
        <div class="loyalty-score-wrap">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 5H4C3.05719 5 2.58579 5 2.29289 5.29289C2 5.58579 2 6.05719 2 7V10.8333V17C2 17.9428 2 18.4142 2.29289 18.7071C2.58579 19 3.05719 19 4 19H16C16.9428 19 17.4142 19 17.7071 18.7071C18 18.4142 18 17.9428 18 17V10.8333V7C18 6.05719 18 5.58579 17.7071 5.29289C17.4142 5 16.9428 5 16 5Z" stroke="#222222"/>
                <path d="M1 12L19 12" stroke="#222222" stroke-linecap="round"/>
                <path d="M10 5L10 18.5" stroke="#222222" stroke-linecap="round"/>
                <path d="M10 4L9.16447 3.02521C8.3062 2.0239 7.21405 1.24991 5.98491 0.771909V0.771909C5.02988 0.400507 4 1.10501 4 2.12972V2.72489C4 3.49364 4.47193 4.18353 5.18841 4.46216L6.57143 5" stroke="#222222" stroke-linecap="round"/>
                <path d="M10 4L10.8355 3.02521C11.6938 2.0239 12.786 1.24991 14.0151 0.771909V0.771909C14.9701 0.400507 16 1.10501 16 2.12972V2.72489C16 3.49364 15.5281 4.18353 14.8116 4.46216L13.4286 5" stroke="#222222" stroke-linecap="round"/>
            </svg>
            <span>У вас <?=$score?></span>
        </div>
        <div class="loyalty-progress-line">
            <div class="loyalty-progress-line-active" style="width:<?=$deltaLine?>%"></div>
        </div>
        <div  class="loyalty-progress-status">
            <?if ($delta>0){?>
                <div>3%</div>
                <div class="info"><?=$delta?>₽ до нового статуса</div>
                <div>5%</div>
            <?}else{?>
                <div></div>
                <div>5%</div>
                <div></div>
            <?}?>
        </div>
    </div>


    <div class="loyalty-card-wrap">
        <div>
            <h3>WALLET-КАРТА</h3>
            <img src="/local/templates/.default/assets/img/walletcard.png">
        </div>
        <?if ($userInfo['UF_LP_ID_INTARO']==''){?>
        <div class="card-add">
            <a href="https://get.osmicards.com/anketa/4851LEV292ELE/get" target="_blank">Добавить карту</a>
            <span>Чтобы узнавать о новинкаx и события бренда</span>
        </div>
        <?}?>
    </div>

    <div class="loyalty-card-wrap-mobile">

            <img src="/local/templates/.default/assets/img/walletcard-m.png">

            <h3>WALLET-КАРТА</h3>

    </div>


<?if ($userInfo['UF_LP_ID_INTARO']==''){?>
    <div class="card-add card-add-mobile">
        <a href="https://get.osmicards.com/anketa/4851LEV292ELE/get" target="_blank">Добавить карту</a><br>
        <span>Чтобы узнавать о новинкаx и события бренда</span>
    </div>
<?}?>


    <?php if ($userInfo['PERSONAL_PHONE']!=''){?>
    <div class="loyalty-qr-code loyalty-qr-code-desktop">
        <a href='#'>Показать индивидуальный QR код</a>
        <div class="qrcode">

        </div>
        <div class="qrcode-text">
            <span style="text-decoration:underline;">Сканировать QR</span><br>
            <span>Покажите QR код на кассе</span>
        </div>

    </div>
    <?}?>

    <div class="loyalty-text">
        Оплачивайте до 30% заказа баллами<br>
        За покупки начисляем 3% баллами (1 балл = 1 рубль)<br>
        Баллы действуют 1 год и не применяются к товарам со скидкой<br>

        <?php if ($userInfo['PERSONAL_PHONE']!=''){?>
            <div class="loyalty-qr-code loyalty-qr-code-mobile">
                <div class="qrcode2">

                </div>

                <span style="text-decoration:underline;">Сканировать QR</span><br>
                <span>Покажите QR код на кассе</span>

            </div>
        <?}?>

        <a href="/personal/about/" style="text-decoration:underline;">Подробнее о системе привилегий</a>
    </div>

    <div class="loyalty-history">
        <h3>История списаний и начислений</h3>
        <table>
            <thead>
                <tr>
                    <th>Бонусы</th>
                    <th>Дата начисления</th>
                    <th>Дата сгорания</th>
                    <th>Основание</th>
                </tr>
            </thead>
            <tbody>
                <?foreach ($bonusHistory as $history){

                    $activefrom=explode(' ',$history['ACTIVE_FROM']);
                    $timestamp=explode(' ',$history['TIMESTAMP_X']);
                    $activeto=explode(' ',$history['ACTIVE_TO']);
                    ?>
                <tr>
                    <td>
                        <?if ($history['TYPE']=='ORDER'){?>+<?=(int)$history['VALUE']?><?}?>
                        <?if ($history['TYPE']=='BONUSPAY'){?><?=(int)$history['VALUE']?><?}?>
                        <?if ($history['TYPE']=='ADMIN'){?>+<?=(int)$history['VALUE']?><?}?>

                        <?if ($history['TYPE']=='RETAILCRM'){?>+<?=(int)$history['VALUE']?><?}?>

                    </td>
                    <td>
                        <?if ($history['TYPE']=='ORDER'){?>
                            <?=$activefrom[0]?>
                        <?}?>

                        <?if ($history['TYPE']=='BONUSPAY'){?>
                            <?=$timestamp[0];?>
                        <?}?>


                    </td>
                    <td>
                        <?=$activeto[0]?>
                    </td>
                    <td>
                        <?if ($history['TYPE']=='ORDER'){?>Заказ № <?=$history['ORDER_ID']?><?}?>
                        <?if ($history['TYPE']=='BONUSPAY'){?>Заказ № <?=$history['ORDER_ID']?><?}?>
                        <?if ($history['TYPE']=='ADMIN'){?>Бонус<?}?>
                        <?if ($history['TYPE']=='RETAILCRM'){?>Бонус<?}?>
                    </td>
                </tr>
                <?}?>
            </tbody>
        </table>


        <div class="mobile-table">
            <?foreach ($bonusHistory as $history){
                $activefrom=explode(' ',$history['ACTIVE_FROM']);
                $timestamp=explode(' ',$history['TIMESTAMP_X']);
                $activeto=explode(' ',$history['ACTIVE_TO']);
                ?>
                <div class="history-row">
                    <div>
                        <div class="history-row-top">
                            <?if ($history['TYPE']=='ORDER'){?>Заказ № <?=$history['ORDER_ID']?><?}?>
                            <?if ($history['TYPE']=='BONUSPAY'){?>Заказ № <?=$history['ORDER_ID']?><?}?>

                            <?if ($history['TYPE']=='ADMIN'){?>Бонус<?}?>
                            <?if ($history['TYPE']=='RETAILCRM'){?>Бонус<?}?>


                        </div>
                        <div class="history-row-info-grey">
                            <?if ($history['TYPE']=='BONUSPAY'){?>Начисление <?=$timestamp[0]?><?}else{?>
                            Начисление <?=$activefrom[0]?>
                            <?}?>
                        </div>
                    </div>
                    <div class="history-row-right">
                        <div  class="history-row-top">
                            <?if ($history['TYPE']=='ORDER'){?>+<?=(int)$history['VALUE']?><?}?>
                            <?if ($history['TYPE']=='BONUSPAY'){?><?=(int)$history['VALUE']?><?}?>
                            <?if ($history['TYPE']=='ADMIN'){?>+<?=(int)$history['VALUE']?><?}?>
                            <?if ($history['TYPE']=='RETAILCRM'){?>+<?=(int)$history['VALUE']?><?}?>
                            баллов
                        </div>
                        <div class="history-row-info-grey"><?if ($history['TYPE']=='ORDER'){?>Сгорание <?=$activeto[0]?><?}?></div>
                    </div>

                </div>
            <?}?>

        </div>



    </div>
</div>
<?php if ($userInfo['PERSONAL_PHONE']!=''){?>
<script>
    $('.qrcode').qrcode({width: 93,height: 93,text: "<?=$userInfo['PERSONAL_PHONE']?>"});
    $('.qrcode2').qrcode({width: 93,height: 93,text: "<?=$userInfo['PERSONAL_PHONE']?>"});

    $('.loyalty-qr-code a').on('click', function(e){
        e.preventDefault();
        $(this).hide();
        $('.qrcode').show();
        $('.qrcode-text').show();


    });
</script>

<?php }?>
