<?php
/**
 * @author: Stanislav Semenov (CJP2600)
 * @email : cjp2600@ya.ru
 *
 * @date  : 26.02.2014
 * @time  : 11:36
 *
 */
namespace Level44;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;


/**
 * Class HLWrapper
 */
class HLWrapper {

	# highloadblock table name
	/**
	 * @var null
	 */
	private static $_hlblock_list;
	# highloadblock table code
	/**
	 * @var null
	 */
	private $_table_name;
	# highloadblock table id
	/**
	 * @var null
	 */
	private $_table_code;
	# default cache time
	/**
	 * @var null
	 */
	private $_table_id;
	# hlblock data
	/**
	 * @var int
	 */
	private $_default_cache = 86400;
	# hl list
	/**
	 * @var null
	 */
	private $_hldata;

	/**
	 * @param $config
	 *
	 * @throws Exception
	 * @throws \Bitrix\Main\LoaderException
	 */
	function __construct($config) {
		# load highloadblock module
		if ( !Loader::includeModule('highloadblock') ) {
			throw new \Exception("highloadblock module not exists");
		}
		# load iblock module
		if ( !Loader::includeModule("iblock") ) {
			throw new \Exception("iblock module not exists");
		}
		# set highloadblock code
		$this->_table_name = (isset($config['table_name']) && !empty($config['table_name'])) ? $config['table_name'] : NULL;
		$this->_table_code = (isset($config['table_code']) && !empty($config['table_code'])) ? $config['table_code'] : NULL;
		$this->_table_id = (isset($config['table_id']) && !empty($config['table_id'])) ? $config['table_id'] : NULL;
	}


	/**
	 * table
	 *
	 * @param $_table_name
	 *
	 * @return $this|null
	 */
	public static function table($_table_name) {
		return new self(["table_name" => $_table_name]);
	}

	/**
	 * table
	 *
	 * @param $_table_code
	 *
	 * @return $this|null
	 */
	public static function code($_table_code) {
		return new self(["table_code" => $_table_code]);
	}

	/**
	 * table
	 *
	 * @param $_table_id
	 *
	 * @return $this|null
	 */
	public static function id($_table_id) {
		return new self(["table_id" => $_table_id]);
	}

	/**
	 * getList
	 *
	 * @param $param
	 *
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public function getList($param = []) {
		$obEntity = $this->getEntityDataClass();

		return $obEntity::getList($param);
	}

	/**
	 * getEntityDataClass
	 * @return \Bitrix\Main\Entity\DataManager
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	private function getEntityDataClass($refresh_cache = false) {
		if ( !is_null($this->getTableName()) ) {
			if ( false === ($hlblock = $this->getHlBlockByTable($refresh_cache)) ) {
				throw new \Exception('Not found HighloadBlock for table = "' . $this->getTableName() . '"');
			}
		} else {
			if ( !is_null($this->getTableCode()) ) {
				if ( false === ($hlblock = $this->getHlBlockByCode($refresh_cache)) ) {
					throw new \Exception('Not found HighloadBlock for name = "' . $this->getTableCode() . '"');
				}
			} else {
				if ( !is_null($this->getTableId()) ) {
					if ( false === ($hlblock = $this->getHlBlockById($refresh_cache)) ) {
						throw new \Exception('Not found HighloadBlock for id = "' . $this->getTableId() . '"');
					}
				}
			}
		}

		$entity = HighloadBlockTable::compileEntity($this->getHldata());
		$entityDataClass = $entity->getDataClass();

		return $entityDataClass;
	}

	/**
	 * @return null
	 */
	public function getTableName() {
		return $this->_table_name;
	}

	/**
	 * getHlBlockByTable
	 * @return array|bool|false
	 * @throws Exception
	 * @internal param bool $refresh_cache
	 */
	private function getHlBlockByTable($refresh_cache = false) {
		$tableName = $this->getTableName();
		if ( !$tableName ) {
			throw new \Exception('table name is empty.');
		}
		$hlblock = false;
		$arHLEnititesList = $this->getHlTablesList($refresh_cache);
		foreach ($arHLEnititesList as $arItem) {
			if ( strtoupper($arItem['TABLE_NAME']) == strtoupper($tableName) ) {
				$hlblock = $arItem;
				break;
			}
		}
		$this->setHldata($hlblock);

		return $hlblock;
	}

	/**
	 * _getHlTablesList
	 *
	 * @param bool $refresh_cache
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getHlTablesList($refresh_cache = false) {
		if ( is_null(self::$_hlblock_list) ) {
			$cache = new \CPHPCache();
			$cache_time = $this->getDefaultCacheTime();
			$cache_id = 'getHlTablesList';
			$cache_path = '/' . __CLASS__ . '/' . __METHOD__ . '/';
			if ( (!$refresh_cache) && $cache->InitCache($cache_time, $cache_id, $cache_path) ) {
				self::$_hlblock_list = $cache->GetVars();
			} else {
				$cache->StartDataCache($cache_time, $cache_id, $cache_path);

				$dbItems = HighloadBlockTable::getList(
					[
						'select' => ['ID', 'NAME', 'TABLE_NAME'],
					]
				);
				while ($arItem = $dbItems->fetch()) {
					self::$_hlblock_list[strtoupper($arItem['NAME'])] = $arItem;
				}

				if ( is_null(self::$_hlblock_list) ) {
					$cache->AbortDataCache();
				}
				$cache->EndDataCache(self::$_hlblock_list);
			}
		}

		return self::$_hlblock_list;
	}

	/**
	 * @return int
	 */
	public function getDefaultCacheTime() {
		return $this->_default_cache;
	}

	/**
	 * @return null
	 */
	public function getTableCode() {
		return $this->_table_code;
	}

	/**
	 * getHlBlockByCode
	 * @return array|bool|false
	 * @throws Exception
	 * @internal param bool $refresh_cache
	 */
	private function getHlBlockByCode($refresh_cache = false) {
		$tableCode = strtoupper(trim($this->getTableCode()));
		if ( !$tableCode ) {
			throw new \Exception('table code is empty.');
		}
		$hlblock = false;
		$arHLEnititesList = $this->getHlTablesList($refresh_cache);
		if ( isset($arHLEnititesList[$tableCode]) ) {
			$hlblock = $arHLEnititesList[$tableCode];
		}
		$this->setHldata($hlblock);

		return $hlblock;
	}

	/**
	 * @return null
	 */
	public function getTableId() {
		return $this->_table_id;
	}

	/**
	 * getHlBlockById
	 * @return bool
	 * @throws Exception
	 */
	private function getHlBlockById($refresh_cache = false) {
		$tableId = $this->getTableId();
		if ( !$tableId ) {
			throw new \Exception('table id is empty.');
		}
		$hlblock = false;
		$arHLEnititesList = $this->getHlTablesList($refresh_cache);
		foreach ($arHLEnititesList as $arItem) {
			if ( $arItem['ID'] == $tableId ) {
				$hlblock = $arItem;
				break;
			}
		}
		$this->setHldata($hlblock);

		return $hlblock;
	}

	/**
	 * @return null
	 */
	public function getHldata() {
		return $this->_hldata;
	}

	/**
	 * @param null $hldata
	 */
	public function setHldata($hldata) {
		$this->_hldata = $hldata;
	}

	/**
	 * add
	 *
	 * @param $param
	 *
	 * @return \Bitrix\Main\Entity\AddResult
	 * @throws \Exception
	 */
	public function add($param) {
		$obEntity = $this->getEntityDataClass();

		return $obEntity::add($param);
	}

	/**
	 * update
	 *
	 * @param       $primary
	 * @param array $data
	 *
	 * @return \Bitrix\Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	public function update($primary, array $data) {
		$obEntity = $this->getEntityDataClass();

		return $obEntity::update($primary, $data);
	}

	/**
	 * delete
	 *
	 * @param $primary
	 *
	 * @return \Bitrix\Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	public function delete($primary) {
		$obEntity = $this->getEntityDataClass();

		return $obEntity::delete($primary);
	}

	/**
	 * getDataType
	 * @return \Bitrix\Main\Entity\DataManager
	 * @throws Exception
	 */
	public function getDataType($refresh_cache = false) {
		return $this->getEntityDataClass($refresh_cache);
	}

	/**
	 * query
	 * @return \Bitrix\Main\Entity\Query
	 */
	public function query() {
		return new \Bitrix\Main\Entity\Query($this->getEntity());
	}

	/**
	 * getEntity
	 * @return \Bitrix\Main\Entity\DataManager
	 * @throws Exception
	 */
	public function getEntity() {
		$obEntity = $this->getEntityDataClass();

		return $obEntity::getEntity();
	}

	/**
	 * getEntityDataClass
	 * @return \Bitrix\Main\Entity\DataManager
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public function isTableExist($refresh_cache = false) {
		if ( !is_null($this->getTableName()) ) {
			return !(false === ($hlblock = $this->getHlBlockByTable($refresh_cache)));
		} else {
			if ( !is_null($this->getTableCode()) ) {
				return !(false === ($hlblock = $this->getHlBlockByCode($refresh_cache)));
			} else {
				if ( !is_null($this->getTableId()) ) {
					return !(false === ($hlblock = $this->getHlBlockById($refresh_cache)));
				}
			}
		}

		return false;
	}
}
