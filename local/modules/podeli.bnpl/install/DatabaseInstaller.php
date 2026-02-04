<?php

namespace Podeli\Bnpl;

use Bitrix\Main\Config\Option;

class DatabaseInstaller
{
    protected $moduleId;
    protected $connection;

    public function __construct($moduleId, $connection)
    {
        $this->moduleId = $moduleId;
        $this->connection = $connection;
    }

    public function install()
    {
        try {
            \Bitrix\Main\ModuleManager::RegisterModule($this->moduleId);
            \Bitrix\Main\Loader::includeModule($this->moduleId);
            $this->connection->startTransaction();
            $this->createRequestTable();
            $this->addEventHandlers();
            $this->connection->commitTransaction();
        } catch (\Exception $ex) {
            $this->connection->rollbackTransaction();
            \Bitrix\Main\ModuleManager::UnregisterModule($this->moduleId);
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
        return true;
    }

    public function uninstall()
    {
        try {
            \Bitrix\Main\Loader::includeModule($this->moduleId);
            $this->connection->startTransaction();
            $this->dropRequestTable();
            $this->dropEventHandlers();
            \Bitrix\Main\ModuleManager::UnregisterModule($this->moduleId);
            $this->connection->commitTransaction();
        } catch (\Exception $ex) {
            $this->connection->rollbackTransaction();
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
        return true;
    }

    protected function createRequestTable()
    {
        $tableName = \Podeli\Bnpl\Orm\RequestTable::getTableName();
        if (!$this->connection->isTableExists($tableName)) {
            $this->connection->createTable(
                \Podeli\Bnpl\Orm\RequestTable::getTableName(),
                \Podeli\Bnpl\Orm\RequestTable::getMap(),
                ['ID'],
                ['ID']
            );
        }
    }

    protected function dropRequestTable()
    {
        $tableName = \Podeli\Bnpl\Orm\RequestTable::getTableName();
        $dropTable = Option::get('podeli.bnpl', 'uninstall_with_db');
        if ($dropTable && $this->connection->isTableExists($tableName)) {
            $this->connection->dropTable(\Podeli\Bnpl\Orm\RequestTable::getTableName());
        }
    }

    protected function addEventHandlers()
    {
    }

    private function dropEventHandlers()
    {
    }
}
