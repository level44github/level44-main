<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main;
use Bitrix\Main\Loader;
use Level44\Base;

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');

Loader::includeModule('sale');

$componentClass = \CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.steps');
if (empty($componentClass)) {
    die();
}

$result = true;
$errors = array();
$data = array();

try
{
    CUtil::JSPostUnescape();

    $request = Main\Context::getCurrent()->getRequest()->getPostList();
    $data = CBitrixLocationSelectorStepsComponent::processSearchRequestV2($_REQUEST);
    $data["ITEMS"] = array_filter(
        $data["ITEMS"],
        function ($item) {
            return !in_array((int)$item["VALUE"], Base::getSngCountriesId(), true);
        });
}
catch(Main\SystemException $e)
{
    $result = false;
    $errors[] = $e->getMessage();
}

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
print(CUtil::PhpToJSObject(array(
    'result' => $result,
    'errors' => $errors,
    'data' => $data
), false, false, true));