<?php

namespace pinst\db\schema;
use pinst\base\Db;
use pinst\exception\DbConnectionException;
use pinst\exception\DbException;

class Mysqli extends Db
{
    private $_statement = null;

    protected function reset() {
        parent::reset ();
        $this->_statement = null;
    }
    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::connection()
     */
    public function connection() {
        $this->_db = new \mysqli ( $this->host, $this->username, $this->password, $this->database, $this->port );
        if ($this->getDb ()->connect_error)
            throw new DbConnectionException( 'Connect Error ' . $this->_db->connect_errno . ': ' . $this->_db->connect_error );

        if ($this->charset)
            $this->getDb ()->set_charset ( $this->charset );
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::close()
     */
    public function close() {
        if ($this->_db) {
            $this->_db->close ();
            unset ( $this->_db );
            $this->_db = null;
        }
    }
    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::beginTransaction()
     *
     */
    public function beginTransaction() {
        if ($this->_transaction_in_progress)
            return false;
        $this->_transaction_in_progress = $this->getDb ()->autocommit ( false );
        return $this->_transaction_in_progress;
    }


    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::commit()
     *
     */
    public function commit() {
        $result = $this->getDb ()->commit ();
        $this->_transaction_in_progress = false;
        $this->getDb ()->autocommit ( true );
        return $result;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::rollback()
     *
     */
    public function rollback() {
        $result = $this->getDb ()->rollback ();
        $this->_transaction_in_progress = false;
        $this->getDb ()->autocommit ( true );
        return $result;
    }


    /**
     * (non-PHPdoc)
     * @see \angel\base\Db::getTableSchema()
     */
    public function getTableSchema($tableName){
        $datas = $this->query("SHOW FULL COLUMNS FROM `{$tableName}`");
        if(empty($datas)){
            return false;
        }
        $result = [];
        foreach ($datas as $row){
            $result[$row['Field']] = $row;
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::insert()
     *
     */
    public function insert($tableName, $columns) {
        $tableName = $this->getTableName ( $tableName );
        return $this->buildInsertSchema ( $tableName, $columns );
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::update()
     *
     */
    public function update($tableName, $columns, $where) {
        $tableName = $this->getTableName ( $tableName );
        if ($where) {
            $this->where ( $where );
        }
        return $this->buildUpdateSchema ( $tableName, $columns );
    }

    /**
     * (non-PHPdoc)
     * @see \angel\base\Db::delete()
     */
    public function delete($tableName,$where){
        $tableName = $this->getTableName ( $tableName );
        if ($where) {
            $this->where ( $where );
        }
        $this->_query = "DELETE FROM `{$tableName}` ";
        return $this->execute();
    }



    /**
     * get dataset
     * @return NULL|multitype:
     */
    protected function processResult() {
        /**
         */
        if (empty ( $this->_statement )) {
            return null;
        }
        $metadata = $this->_statement->result_metadata ();

        if (! $metadata && $this->_statement->sqlstate) {
            return [ ];
        }

        $row = array ();
        $parameters = [ ];
        $results = [ ];
        $shouldStoreResult = false;

        while ( $field = $metadata->fetch_field () ) {
            if ($field->type == 255)
                $shouldStoreResult = true;
            $row [$field->name] = null;
            $parameters [] = & $row [$field->name];
        }
        // avoid out of memory bug in php 5.2 and 5.3. Mysqli allocates lot of memory for long*
        // and blob* types. So to avoid out of memory issues store_result is used
        // https://github.com/joshcam/PHP-MySQLi-Database-Class/pull/119
        if ($shouldStoreResult)
            $this->_statement->store_result ();
        call_user_func_array ( array (
            $this->_statement,
            'bind_result'
        ), $parameters );
        $this->totalCount = 0;
        $count = 0;

        while ( $this->_statement->fetch () ) {
            $x = array ();
            foreach ( $row as $key => $val ) {
                if (is_array ( $val )) {
                    foreach ( $val as $k => $v )
                        $x [$key] [$k] = $v;
                } else
                    $x [$key] = $val;
            }
            $count ++;
            array_push ( $results, $x );
        }
        if ($shouldStoreResult)
            $this->_statement->free_result ();
        $this->_statement->close ();
        if ($this->getDb ()->more_results ())
            $this->getDb ()->next_result ();
        return $results;
    }

    public function query($sql){
        $this->_query = $sql;
        $this->execTime = microtime(true);
        $this->_statement = $this->prepareQuery ();
        if ($this->_statement->execute ()) {
            $data = $this->processResult ();
            $this->reset ();
            $this->execTime = microtime(true) - $this->execTime;
            return $data;
        } else {
            $this->setLastError ( $this->_statement->error );
            $this->reset ();
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::query()
     *
     */
    public function select() {
        $this->execTime = microtime(true);
        $this->buildQuery ();
        $this->_statement = $this->prepareQuery ();
        if ($this->_statement->execute ()) {
            $data = $this->processResult ();
            $this->reset ();
            $this->execTime = microtime(true) - $this->execTime;
            return $data;
        } else {
            $this->setLastError ( $this->_statement->error );
            $this->reset ();
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::find()
     *
     */
    public function find() {
        $this->limit(1);
        $this->execTime = microtime(true);
        $this->buildQuery ();
        $this->_statement = $this->prepareQuery ();
        if ($this->_statement->execute ()) {
            $data = $this->processResult ();
            $this->reset ();
            $this->execTime = microtime(true) - $this->execTime;
            return isset ( $data [0] ) ? $data [0] : null;
        } else {
            $this->setLastError ( $this->_statement->error );
            $this->reset ();
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \angel\base\Db::execute()
     *
     */
    public function execute() {
        $this->execTime = microtime(true);
        $this->buildQuery ();
        $this->_statement = $this->prepareQuery ();
        if ($this->_statement->execute ()) {
            $rows = $this->_statement->affected_rows;
            $this->reset ();
            $this->execTime = microtime(true) - $this->execTime;
            return $rows;
        } else {
            $this->setLastError ( $this->_statement->error );
            $this->reset ();
            return false;
        }
    }

    protected function prepareQuery() {
        $this->log ( "prepare sql statement : {$this->_query}" );
        if (! $stmt = $this->getDb ()->prepare ( $this->_query ))
            throw new DbException( "Problem preparing query ($this->_query) " . $this->getDb ()->error );
        return $stmt;
    }
}