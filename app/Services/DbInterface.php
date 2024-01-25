<?php

namespace App\Services;


use Simplon\Postgres\Postgres;
use Simplon\Postgres\PostgresException;
use Simplon\Postgres\PostgresQueryIterator;

/**
 * Postgres
 * @package Simplon\Postgres
 * @author Tino Ehrich (tino@bigpun.me)
 */
interface DbInterface
{
    /**
     * @return Postgres
     */
    public function close();

    /**
     * @return bool|int
     */
    public function getRowCount();

    /**
     * @param $query
     *
     * @return bool
     * @throws PostgresException
     */
    public function executeSql($query);

    /**
     * @param $dbName
     *
     * @return bool
     * @throws PostgresException
     */
    public function selectDb($dbName);

    /**
     * @param $query
     * @param array $conds
     *
     * @return false|string
     */
    public function fetchColumn($query, array $conds = array());

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchColumnMany($query, array $conds = array());

    /**
     * @param $query
     * @param array $conds
     *
     * @return PostgresQueryIterator
     */
    public function fetchColumnManyCursor($query, array $conds = array());

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchRow($query, array $conds = array());

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchRowMany($query, array $conds = array());

    /**
     * @param $query
     * @param array $conds
     *
     * @return PostgresQueryIterator
     */
    public function fetchRowManyCursor($query, array $conds = array());

    /**
     * @param string $tableName
     * @param array $data
     * @param string $pkName
     * @param bool $insertIgnore
     *
     * @return int|bool
     * @throws PostgresException
     */
    public function insert($tableName, array $data, $pkName = null, $insertIgnore = false);

    /**
     * @param string $tableName
     * @param array $data
     * @param string $pkName
     * @param bool $insertIgnore
     *
     * @return array|bool
     * @throws PostgresException
     */
    public function insertMany($tableName, array $data, $pkName = null, $insertIgnore = false);

    /**
     * @param $tableName
     * @param array $conds
     * @param array $data
     * @param null $condsQuery
     *
     * @return bool
     * @throws PostgresException
     */
    public function update($tableName, array $conds, array $data, $condsQuery = null);

    /**
     * @param $tableName
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool
     */
    public function delete($tableName, array $conds = array(), $condsQuery = null);
}
