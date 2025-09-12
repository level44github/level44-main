<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var array $arResult
 * @var array $APPLICATION
 */

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();

$queryParams = $request->getQueryList()->toArray();
$queryParams['auth-form'] = 'Y';
$queryParams['backurl'] = urlencode($request->getRequestUri());

$arResult['LINK_TO_LOGIN'] = SITE_DIR . '?' . http_build_query($queryParams);