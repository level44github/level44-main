<?php

namespace Level44;

use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Catalog\Model\Product;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\PersonType;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;

Loc::loadMessages(__FILE__);


class PreOrder
{

    private $alreadySubscribe = false;
    private $productId = false;
    private $siteId = false;
    private $languageId = false;
    private $errors = [];
    private $success = false;
    private $email = [];
    private $phone = [];
    private $userId = false;
    private $isAnonUser = false;
    const ORDER_STATUS_ID = "PO";

    public function __construct($productId, $siteId = SITE_ID)
    {
        $this->productId = $productId;
        $this->siteId = $siteId;
        $this->setLanguageId();
        $this->setUserId();
    }

    public static function OnAdminIBlockElementEditHandler()
    {
        return array(
            "TABSET" => "pre_order",
            "Check" => array(__CLASS__, 'checkFields'),
            "Action" => array(__CLASS__, 'saveData'),
            "GetTabs" => array(__CLASS__, 'getTabs'),
            "ShowTab" => array(__CLASS__, 'showTab'),
        );
    }

    public static function OnEpilogHandler()
    {
        global $APPLICATION;
        if ($APPLICATION->GetCurPage() !== "/bitrix/admin/sale_order_view.php") {
            return true;
        }

        $request = Context::getCurrent()->getRequest();
        $orderId = $request->get("ID");
        if ((int)$orderId <= 0 || !self::isPreOrder($orderId)) {
            return true;
        }

        \CJSCore::Init(array("jquery"));
        ?>
        <script>
            $(document).ready(function () {
                var orderId = Number("<?=$orderId?>");
                var popup = new BX.CDialog({
                    title: "Подтверждение заказа",
                    content: "",
                    icon: 'head-block',
                    resizable: false,
                    draggable: true,
                    height: '70',
                    width: '300',
                    buttons: [
                        "<input type='button' class='js-adm-preorder-close' value='Закрыть'/>",
                    ]
                });

                $('.adm-detail-toolbar').find('.adm-detail-toolbar-right').prepend("<a href='#' class='adm-btn js-adm-preorder' id='PREORDER_btn'>Отправить подтверждение</a>");
                $(document).on("click", ".js-adm-preorder", function (e) {
                    e.preventDefault();

                    BX.showWait();
                    var data = {
                        orderId: orderId,
                        type: "adminConfirm",
                        sessid: BX.bitrix_sessid(),
                    };

                    $.ajax({
                        type: "POST",
                        url: "/ajax/preOrder.php",
                        data: data,
                        dataType: "json",
                        success: function (data) {
                            BX.closeWait();
                            if (data.success) {
                                popup.SetContent("<p>Подтверждение заказа успешно отправлено</p>")
                                $(this).hide();
                            } else {
                                popup.SetContent("<p>Произошла ошибка, повторите попытку.</p>")
                            }
                            popup.Show();
                        },
                        error: function () {
                            BX.closeWait();
                            popup.SetContent("<p>Произошла ошибка, повторите попытку.</p>")
                            popup.Show();
                        }
                    });
                });

                $(document).on("click", ".js-adm-preorder-close", function (e) {
                    e.preventDefault();
                    popup.Close();
                })
            });
        </script>
        <?
        return true;
    }

    public static function getTabs($iblockElementInfo)
    {
        $showTab = false;

        $request = Context::getCurrent()->getRequest();
        $iBlockId = (int)$iblockElementInfo["IBLOCK"]["ID"];

        $showTab = Base::CATALOG_IBLOCK_ID === $iBlockId
            && $iblockElementInfo["ID"] > 0 &&
            (
                !isset($request['action']) ||
                $request['action'] != 'copy');

        return $showTab ? array(
            array(
                "DIV" => "pre_order",
                "SORT" => 10,
                "TAB" => "Предзаказы",
                "TITLE" => "Предзаказы",
            ),
        ) : null;
    }

    public static function showTab($div, $iblockElementInfo)
    {
        ?>
        <tr id="tr_PREORDERS">
            <td colspan="2">
                <?
                $productsId = self::getOffersId($iblockElementInfo["ID"]);

                $sTableID = "tbl_pre_order";
                $lAdmin = new \CAdminList($sTableID);

                $arHeaders = array(
                    array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
                    array("id" => "DATE_INSERT", "content" => "Дата создания", "sort" => "DATE_INSERT", "default" => true),
                    array("id" => "PRICE", "content" => "Сумма", "sort" => "PRICE", "default" => true),
                    array("id" => "LID", "content" => "Сайт", "sort" => "LID", "default" => true),
                );


                $lAdmin->AddHeaders($arHeaders);

                if (!empty($productsId)) {
                    $rsData = \Bitrix\Sale\Order::getList([
                        'filter' => [
                            'BASKET.PRODUCT_ID' => $productsId,
                            'STATUS_ID' => "PO"
                        ],
                        'order' => [
                            'ID' => 'DESC'
                        ],
                        'select' => [
                            "ID",
                            "DATE_INSERT",
                            "STATUS_ID",
                            "PAYED",
                            "CANCELED",
                            "DEDUCTED",
                            "PRICE",
                            "LID",
                        ],
                        'count_total' => true,
                    ]);
                } else {
                    $rsData = [];
                }

                $rsData = new \CAdminResult($rsData, $sTableID);

                while ($arRes = $rsData->NavNext(true, "f_")) {
                    $row = $lAdmin->AddRow($arRes["ID"], $arRes);
                    $arActions = Array();

                    $arActions[] = array(
                        "ICON" => "list",
                        "TEXT" => "Просмотр заказа",
                        "ACTION" => $lAdmin->ActionRedirect("sale_order_view.php?ID={$arRes["ID"]}&lang=" . LANGUAGE_ID),
                        "DEFAULT" => true
                    );

                    $row->AddActions($arActions);
                }

                $lAdmin->CheckListMode();
                $lAdmin->DisplayList();

                ?>
            </td>
        </tr>
        <?
    }


    public static function checkFields()
    {
        return true;
    }

    public static function saveData()
    {
        return true;
    }

    public function setLanguageId()
    {
        $request = Context::getCurrent()->getRequest();
        $isAjax = $request->isAjaxRequest() || $request->isPost();
        if (!$isAjax) {
            $this->languageId = LANGUAGE_ID;
            return;
        }

        if (empty($this->siteId)) {
            return;
        }

        $site = \CSite::GetByID($this->siteId)->GetNext();
        $this->languageId = $site["LANGUAGE_ID"] ? $site["LANGUAGE_ID"] : LANGUAGE_ID;
    }

    public function setUserId()
    {
        global $USER;
        $this->userId = $USER->GetID();

        if (!$this->userId) {
            $this->userId = \CSaleUser::GetAnonymousUserID();
            $this->isAnonUser = true;
        }
    }

    public static function getOffersId($productId)
    {
        $productId = (int)$productId;

        if ($productId <= 0) {
            return false;
        }

        $product = Product::getList(
            [
                "filter" => [
                    "id" => $productId
                ]
            ]
        )->fetch();

        if ((int)$product["TYPE"] !== 3) {
            return [
                $productId
            ];

        }

        $offers = \CCatalogSku::getOffersList($productId);

        return array_keys($offers[$productId]);
    }

    public function getSubscribeButton()
    {
        $langCodes = ["LPO_PREORDER"];
        $button = <<<BUTTONS
                        <button class="btn btn-block mb-4 btn-outline-dark bx-catalog-subscribe-button js-open-subscribe"
                                data-toggle="modal" data-target="#subscribe-modal"
                                type="button">
                            <svg width="14" height="16" viewBox="0 0 14 16" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.5791 12.624H8.46289C8.4082 13.5469 7.82031 14.1348 6.99316 14.1348C6.17285 14.1348 5.57812 13.5469 5.53027 12.624H4.46387C4.51855 13.9365 5.55078 15.0918 6.99316 15.0918C8.44238 15.0918 9.47461 13.9434 9.5293 12.624H12.4141C13.0566 12.624 13.4463 12.2891 13.4463 11.7969C13.4463 11.1133 12.749 10.498 12.1611 9.88965C11.71 9.41797 11.5869 8.44727 11.5322 7.66113C11.4844 4.96777 10.7871 3.22461 8.96875 2.56836C8.73633 1.67969 8.00488 0.96875 6.99316 0.96875C5.98828 0.96875 5.25 1.67969 5.02441 2.56836C3.20605 3.22461 2.50879 4.96777 2.46094 7.66113C2.40625 8.44727 2.2832 9.41797 1.83203 9.88965C1.2373 10.498 0.546875 11.1133 0.546875 11.7969C0.546875 12.2891 0.929688 12.624 1.5791 12.624ZM1.87305 11.5918V11.5098C1.99609 11.3047 2.40625 10.9082 2.76172 10.5049C3.25391 9.95801 3.48633 9.08301 3.54785 7.74316C3.60254 4.7627 4.49121 3.80566 5.66016 3.49121C5.83105 3.4502 5.92676 3.36133 5.93359 3.19043C5.9541 2.47266 6.36426 1.97363 6.99316 1.97363C7.62891 1.97363 8.03223 2.47266 8.05957 3.19043C8.06641 3.36133 8.15527 3.4502 8.32617 3.49121C9.50195 3.80566 10.3906 4.7627 10.4453 7.74316C10.5068 9.08301 10.7393 9.95801 11.2246 10.5049C11.5869 10.9082 11.9902 11.3047 12.1133 11.5098V11.5918H1.87305Z"
                                      fill="#212121"/>
                            </svg>
                            <span>#LPO_PREORDER#</span>
                        </button>
BUTTONS;

        return $this->replaceLang($langCodes, $button);
    }

    public function getAlreadySubscribedButton()
    {
        $langCodes = ["LPO_ALREADY_SUBSCRIBED"];
        $button = <<<BUTTONS
            <span class="btn btn-block mb-4 btn-outline-dark bx-catalog-subscribe-button disabled" type="button">
                            <span>#LPO_ALREADY_SUBSCRIBED#</span>
                        </span>
BUTTONS;

        return $this->replaceLang($langCodes, $button);
    }

    public function getInitJSData()
    {
        return \CUtil::PhpToJSObject($this->getInitData());
    }

    public function getInitData()
    {
        return [
            "button" => $this->check() ?
                $this->getAlreadySubscribedButton() : $this->getSubscribeButton(),
            "check" => $this->check()
        ];
    }

    public function setSessionProducts()
    {
        $_SESSION['PRE_ORDER_PRODUCT'][$this->siteId][$this->productId] = true;
    }


    public function check()
    {
        if ($this->existOrder()) {
            return true;
        }

        if (empty($_SESSION['PRE_ORDER_PRODUCT'])) {
            return false;
        }

        if (array_key_exists($this->productId, $_SESSION['PRE_ORDER_PRODUCT'][$this->siteId])) {
            return true;
        }
    }

    public function replaceLang($langCodes, $template)
    {
        $search = [];
        $replace = [];
        foreach ($langCodes as $langCode) {
            $search[] = "#$langCode#";
            $replace[] = $this->getMessage($langCode);
        }

        return str_replace($search, $replace, $template);
    }

    public function getMessage($code)
    {
        return Loc::getMessage($code, null, $this->languageId);
    }

    public function setFields($fields)
    {
        try {
            if (empty($fields["email"])) {
                throw new \Exception($this->getMessage("LPO_EMAIL_EMPTY"));
            }

            if (!preg_match("/@/i", $fields["email"])) {
                throw new \Exception($this->getMessage("LPO_INVALID_EMAIL"));
            }

            $this->email = $fields["email"];

            if (empty($fields["phone"])) {
                throw new \Exception($this->getMessage("LPO_PHONE_EMPTY"));
            }

            if (!preg_match("/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/i", $fields["phone"])) {
                throw new \Exception($this->getMessage("LPO_INVALID_PHONE"));
            }
            $this->phone = $fields["phone"];
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    public function subscribed()
    {
        if (!empty($this->errors)) {
            return;
        }

        if ($this->check()) {
            $this->success = true;
        }

        $order = Order::create($this->siteId, $this->userId);
        $order->setPersonTypeId($this->getPersonType());
        $this->setProperties($order);
        $basket = $this->createBasket();
        $order->setBasket($basket);
        $order->setField('CURRENCY', CurrencyManager::getBaseCurrency());
        $order->doFinalAction(true);
        $order->setField("STATUS_ID", self::ORDER_STATUS_ID);
        $result = $order->save();
        if ($result->getId()) {
            $this->initPaySystem($order);
            $this->setSessionProducts();
            $this->success = true;
        }
    }


    public function setProperties(Order $order)
    {
        $propertyCollection = $order->getPropertyCollection();
        foreach ($propertyCollection as $property) {
            switch ($property->getField("CODE")) {
                case "EMAIL":
                    $property->setValue($this->email);
                    break;
                case "PHONE":
                    $property->setValue($this->phone);
                    break;
            }
        }
    }

    public function initPaySystem(Order $order)
    {
        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem();
        $payment->setField('SUM', $order->getPrice());
        $paySystems = PaySystemManager::getListWithRestrictions($payment);
        foreach ($paySystems as $paySystem) {
            if ($paySystem["ACTIVE"] && $paySystem["IS_CASH"] !== "Y") {
                $payment->setField('PAY_SYSTEM_ID', $paySystem["ID"]);
                $payment->setField('PAY_SYSTEM_NAME', $paySystem["NAME"]);
                $payment->save();
                break;
            }
        }
    }

    public function getPersonType()
    {
        $personTypes = PersonType::load($this->siteId);

        return (int)reset($personTypes)["ID"];
    }

    public function createBasket()
    {
        $basket = null;

        $basket = \Bitrix\Sale\Basket::create($this->siteId);

        $item = $basket->createItem('catalog', $this->productId);
        $item->setFields(array(
            'QUANTITY' => 1,
            'SUBSCRIBE' => "Y",
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => $this->siteId,
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ));

        return $basket;
    }

    public function existOrder()
    {
        if ($this->isAnonUser) {
            return false;
        }

        $orders = Order::getList(
            [
                "filter" =>
                    [
                        "LID" => $this->siteId,
                        "STATUS_ID" => self::ORDER_STATUS_ID,
                        "USER_ID" => $this->userId,
                        'BASKET.PRODUCT_ID' => $this->productId,
                    ]
            ]
        )->fetchAll();

        return (bool)count($orders);
    }

    public function isSuccess()
    {
        return $this->success === true;
    }

    public function getError()
    {
        return reset($this->errors);
    }

    public static function isPreOrder($orderId)
    {
        if ((int)$orderId <= 0) {
            return false;
        }

        $order = Order::load($orderId);

        if (!$order) {
            return false;
        }

        return $order->getField("STATUS_ID") === self::ORDER_STATUS_ID;
    }
}