<?php

namespace pdonosql;

/**
 * help to communicate with a database without writing any SQL query. \
 * NOTE: aliases can be used right after table names or before field names
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
class PDONoSQL {
    private $connection;
    public function __construct(\PDO $connection) {
        $this->setConnection( $connection );
        $this->clear();
    }

    public function setConnection(\PDO $connection) {
        $this->connection = $connection;
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * clear the query of all the informations \
     * like the main table, the joints, the order...
     * It prepares the object for another query
     */
    public function clear() {
        $this->main_table = null;
        $this->exception = null;

        $this->joints = '';
        $this->where = null;
        $this->having = null;
        $this->group = '';
        $this->order = '';

        return $this;
    }

    private function _check(){
        if( is_null($this->main_table) )
            throw new \Exception("main table required; use 'in()'");
    }

    /* handle exception */
    private $exception;
    /** return any exception raised while processing the query */
    public function getException(): \Exception{
        return $this->exception; }
    /** check if the query was processed without exception */
    public function isOK(){
        return ($this->exception === null); }



    /* set table */
    private $main_table;
    /**
     * define the main table
     * equivalent to SQL 'FROM' or 'INTO'.
     *
     * @param string $main_table the table on which any query will executed primarily
     */
    public function in( string $main_table ){
        $this->main_table = utils\Utils::noInject( $main_table );
        return $this;
    }


    /* handle joints */
    /** for LEFT JOIN,
     * i.e take all of the rows in the other table(s) and
     * only rows of that table which have a relation with those rows . \
     * \
     * the missing data in the result will be NULL
     */
    const PRIORITY_LOW = 1;
    /** [DEFAULT] for INNER JOIN,
     * i.e take in all the tables only rows that are
     * related to at least one row in another table. \
     * \
     * the rows which have no link to any row in the other tables
     * will be omitted.
     */
    const PRIORITY_NORMAL = 2;
    /** for RIGHT JOIN,
     * i.e take all of the rows in the table and
     * only rows of other table(s) ehich have a relation with those rows . \
     * \
     * the missing data in the result will be NULL
     */
    const PRIORITY_HIGH = 4;

    private $joints;
    /**
     * specify another table to consult too, like SQL '* JOIN'.
     *
     * $link_from and $link_to are each in different table,
     * no matter which order you mention them. \
     * one must follow the pattern '[table].[column]'
     * if the column exit in more than one table.
     *
     * omit $link_to or $link_from for NATURAL JOIN,
     * i.e join on identical columns names. \
     * Caution: natural join can be unpredictable!
     *
     * @param string $table the other table to consult
     * @param string $link_from the column of one table
     * @param string $link_to the column of the second table
     * @param int $priority a 'PRIORITY_*' representing the joint
     * */
    public function andIn( string $table,
        string $link_from=null, string $link_to=null,
        int $priority=self::PRIORITY_NORMAL ){

        $table = utils\Utils::noInject( $table );
        if( $table !== $this->main_table){
            if( $priority == self::PRIORITY_LOW ) $joint = 'LEFT';
            else if( $priority == self::PRIORITY_HIGH ) $joint = 'RIGHT';
            else $joint = 'INNER';

            $joint .= ' JOIN '.$table;

            if( is_null($link_to) || is_null($link_from) )
                $joint = preg_replace('#^[^\s]+#', 'NATURAL', $joint);
            else $joint .= ' ON '.utils\Utils::noInject( $link_from ).' = '.utils\Utils::noInject( $link_to );

            $this->joints = "\n".$joint;
        }
        return $this;
    }

    /* handle conditions */
    private $where;
    private $having;
    /**
     * define conditions for the query to be processed. \
     * those conditions apply generally on data in tables. \
     * But with 'create' query (INSERT) the conditions verify data inserted.
     *
     * @param \pdonosql\condition\_PDONoSQLCondition $condition the condition
     * @param bool $checks_table_columns specifies if the conditions apply on table column's
     */
    public function if( condition\_PDONoSQLCondition $condition, bool  $checks_table_columns = true ){
        if($checks_table_columns) $this->where = $condition;
        else $this->having = $condition;

        return $this;
    }

    /* handle groups by */
    private $group = '';
    /**
     * group the resulting row by data using column names in order.
     *
     * @param string[] ...$columns the comma-separated list of columns name
     */
    public function groupBy( string ...$columns ) {
        $this->group = ' GROUP BY '.join( ', ',
            array_map(function( $col ){ return utils\Utils::noInject( $col ); }, $columns) );
        return $this;
    }

    /* handle order by */
    private $order = '';
    /**
     * order the resulting row by data using column namesin order.
     *
     * @param string[] ...$columns the comma-separated list of columns name
     */
    public function orderBy( string ...$columns ) {
        $this->order = ' ORDER BY '.join(', ',
            array_map(function( $col ){ return utils\Utils::noInject( $col ); }, $columns) );
        return $this;
    }

    /* handle order by */
    private $limit = '';
    /**
     * take only a set of data in the result
     *
     * @param int $row_count the number of rows the extract
     * @param int $from_row the row index from which to start (like array index)
     */
    public function takeOnly( int $row_count, int $from_row=0 ) {
        $this->limit = ' LIMIT '.$from_row.', '.$row_count;
        return $this;
    }


    /* CRUD ACTIONS */
    /**
     * insert a new row in the database. \
     * execute the 'create' (SQL INSERT) query.
     *
     * @param array $data the row data to insert in the table.
     * an associated array with table columns as keys.
     *
     * @return bool specifies if the insertion succeeded
     */
    public function create( array $data ): bool {
        $this->exception = null;
        try{
            if( empty($data) ) throw new \Exception("empty data received!", 1);

            if( !is_null($this->where) && !$this->where->eval($data) )
                throw new \Exception("the data doesn't match the requirements", 1);


            $columns = []; $values = [];

            foreach ($data as $col => $val) {
                array_push( $columns, utils\Utils::noInject( $col ) );

                if( is_string($val) ) $val = '"'.$val.'"';
                array_push( $values, $val );
            }

            $columns = join(', ', $columns);
            $values = join(', ', $values);

            $query = 'INSERT INTO '.$this->main_table.'('.$columns.') '.
                'VALUES('.$values.')';

            return ($this->connection->exec($query) > 0);
        }catch(\Exception $e){
            $this->exception = $e;
            return false;
        }
    }

    private function _build_read( string ...$columns ): string {
        $columns = join(', ',
            array_map(function( $col ){ return utils\Utils::noInject( $col ); }, $columns));
        if( strlen($columns) < 1 ) $columns = '*';

        $where = '';
        if( !is_null($this->where) )
            $where = ' WHERE '.$this->where->toString();

        $having = '';
        if( !is_null($this->having) )
            $having = ' HAVING '.$this->having->toString();

        return $columns.
            ' FROM '.$this->main_table.
            $this->joints.
            $where.
            $this->group.
            $having.
            $this->order.
            $this->limit;
    }

    /**
     * retrieve informations from the database. \
     * execute the 'read' (SQL SELECT) query.
     *
     * @param string[] ...$columns the columns to read
     *
     * @return array the array of associated-arrays (rows) read
     */
    public function read( string ...$columns ): array {
        $this->_check();

        $this->exception = null;
        try{
            $query = 'SELECT '.$this->_build_read( ...$columns );
            $query = $this->connection->query($query);

            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $query->closeCursor();

            return $result;
        }catch(\Exception $e){
            $this->exception = $e;
            return [];
        }
    }

    /**
     * retrieve informations from the database and
     * take only one occurrence of a set of data (if available many times) \
     * execute the 'readOnce' (SQL SELECT DISTINCT) query.
     *
     * @param string[] ...$columns the columns to read
     *
     * @return array the array of associated-arrays (rows) read
     */
    public function readOnce( string ...$columns ): array /* read data in assoc-array */ {
        $this->_check();

        $this->exception = null;
        try{
            $query = 'SELECT DISTINCT '.$this->_build_read( ...$columns );
            $query = $this->connection->query($query);

            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $query->closeCursor();

            return $result;
        }catch(\Exception $e){
            $this->exception = $e;
            return [];
        }
    }

    /**
     * update informations in the database. \
     * execute the 'update' (SQL UPDATE) query.
     *
     * @param array $data the data to insert as update
     * @param \pdonosql\condition\_PDONoSQLCondition|null $data_check
     * the condition to verify on data for their inserting
     *
     * @return int the number of rows updated
     */
    public function update( array $data, condition\_PDONoSQLCondition $data_check = null ): int {
        $this->exception = null;
        try{
            if( empty($data) ) throw new \Exception("empty data received!", 1);

            if( ! is_null($data_check) )
                if( !$data_check->eval( $data ) )
                    throw new \Exception("the data doesn't match the requirements", 1);

            $sets = [];
            foreach ($data as $col => $val) {
                if( is_string($val) ) $val = '"'.$val.'"';
                array_push( $sets, utils\Utils::noInject( $col ).' = '.$val);
            }
            $sets = join(', ', $sets);

            $where = '';
            if( !is_null($this->where) )
                $where = ' WHERE '.$this->where->toString();

            $query = 'UPDATE '.$this->main_table.
                ' SET '.$sets.
                $where;

            return $this->connection->exec($query);
        }catch(\Exception $e){
            $this->exception = $e;
            return 0;
        }
    }

    /**
     * delete informations from the database. \
     * execute the 'delete' (SQL DELETE) query.
     *
     * @return int the number of rows deleted
     */
    public function delete(): int {
        $this->exception = null;
        try{
            $where = '';
            if( !is_null($this->where) )
                $where = ' WHERE '.$this->where->toString();

            $query = 'DELETE FROM '.$this->main_table.$where;

            return $this->connection->exec($query);
        }catch(\Exception $e){
            $this->exception = $e;
            return 0;
        }
    }


    /**
     * retrieve all the table names in the database
     *
     * @return array the array of table names
     */
    public function getTables(): array {
        $this->exception = null;
        try{
            $query = 'SHOW TABLES';
            $query = $this->connection->query($query);

            $result = $query->fetchAll(\PDO::FETCH_COLUMN);
            $query->closeCursor();

            return $result;
        }catch(\Exception $e){
            $this->exception = $e;
            return [];
        }
    }

    /**
     * retrieve a table description in the database.\
     * for each field, gets the informations: Field, Type
     * Null, Key, Default, Extra (the extra informations)
     *
     * @return array the array of field description (associative-array)
     */
    public function getFields(): array{
        $this->_check();

        $this->exception = null;
        try{
            $query = 'DESCRIBE '.$this->main_table;
            $query = $this->connection->query($query);

            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $query->closeCursor();

            return $result;
        }catch(\Exception $e){
            $this->exception = $e;
            return [];
        }
    }
};
