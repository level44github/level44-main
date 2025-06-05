<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$arUrlRewrite=array (
  0 =>
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  2 =>
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  1 =>
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
    3 =>
        array (
            'CONDITION' => '#^' . SITE_DIR . 'catalog/sale/filter/(.+?)/apply/#',
            'RULE' => 'SMART_FILTER_PATH=$1',
            'ID' => '',
            'PATH' => SITE_DIR . 'catalog/sale/index.php',
            'SORT' => 99,
        ),
  4 =>
  array (
      'CONDITION' => '#^' . SITE_DIR . 'catalog/sale/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog',
      'PATH' => SITE_DIR . 'catalog/sale/index.php',
      'SORT' => 100,
  ),
  5 =>
    array (
        'CONDITION' => '#^' . SITE_DIR . 'catalog/filter/(.+?)/apply/#',
        'RULE' => 'SMART_FILTER_PATH=$1',
        'ID' => '',
        'PATH' => SITE_DIR . 'catalog/index.php',
        'SORT' => 99,
        ),
  7 =>
  array (
      'CONDITION' => '#^' . SITE_DIR . 'catalog/#',
      'RULE' => '',
      'ID' => 'bitrix:catalog',
      'PATH' => SITE_DIR . 'catalog/index.php',
      'SORT' => 100,
  ),

  6 =>
  array (
      'CONDITION' => '#^' . SITE_DIR . 'search/filter/(.+?)/apply/#',
      'RULE' => 'SMART_FILTER_PATH=$1',
      'ID' => '',
      'PATH' => SITE_DIR . 'search/index.php',
      'SORT' => 100,
  ),
);
