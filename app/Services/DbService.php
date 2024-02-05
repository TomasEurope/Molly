<?php

namespace App\Services;

use Minicli\App;
use Minicli\ServiceInterface;
use Simplon\Postgres\Manager\PgSqlManager;
use Simplon\Postgres\Manager\PgSqlQueryBuilder;
use Simplon\Postgres\Postgres;

class DbService implements ServiceInterface, DbInterface
{

    private Postgres $db;
    private PgSqlManager $pgSqlManager;

    final public function load(App $app): void
    {

        $host = $app->config->db['host'];
        $user = $app->config->db['user'];
        $password = $app->config->db['pass'];
        $database = $app->config->db['data'];
        $port = $app->config->db['port'] ?: 5432;
        $fetchMode = $app->config->db['mode'] ?: \PDO::FETCH_ASSOC;
        $charset = $app->config->db['char'] ?: 'utf8';
        $options = array();

        //$this->db = new Postgres($host, $user, $password, $database, $port, $fetchMode, $charset, $options);

        //$this->pgSqlManager = new PgSqlManager($this->db);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->db->$name(...$arguments);
    }

    public function close()
    {
        return $this->db->close();
    }

    public function getRowCount()
    {
        return $this->db->getRowCount();
    }

    public function executeSql($query)
    {
        return $this->db->executeSql($query);
    }

    public function selectDb($dbName)
    {
        return $this->db->selectDb($dbName);
    }

    public function fetchColumn($query, array $conds = array())
    {
        return $this->db->fetchColumn($query, $conds);
    }

    public function fetchColumnMany($query, array $conds = array())
    {
        return $this->db->fetchColumnMany( $query, $conds);
    }

    public function fetchColumnManyCursor($query, array $conds = array())
    {
        return $this->db->fetchColumnManyCursor($query, $conds);
    }

    public function fetchRow($query, array $conds = array())
    {
        return $this->db->fetchRow($query, $conds);
    }

    public function fetchRowMany($query, array $conds = array())
    {
        return $this->db->fetchRowMany($query, $conds);
    }

    public function fetchRowManyCursor($query, array $conds = array())
    {
        return $this->db->fetchRowManyCursor($query, $conds);
    }

    public function insert($tableName, array $data, $pkName = null, $insertIgnore = false, $upsert = null)
    {
        return $this->db->insert($tableName, $data, $pkName, $insertIgnore, $upsert);
    }

    public function insertMany($tableName, array $data, $pkName = null, $insertIgnore = false)
    {
        return $this->db->insertMany($tableName, $data, $pkName, $insertIgnore);
    }

    public function update($tableName, array $conds, array $data, $condsQuery = null)
    {
        return $this->db->update($tableName, $conds, $data, $condsQuery);
    }

    public function delete($tableName, array $conds = array(), $condsQuery = null)
    {
        return $this->db->delete($tableName, $conds, $condsQuery);
    }

}
