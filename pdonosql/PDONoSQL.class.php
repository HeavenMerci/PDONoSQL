<?php
/**
 * helps to communicate with a database without writing any SQL query.
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql;

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
    public function getException(): \Exception{
        return $this->exception; }
    public function isOK(){
        return ($this->exception === null); }



    /* set table */
    private $main_table;
    public function in( string $main_table ){
        $this->main_table = utils\Utils::noInject( $main_table );
        return $this;
    }


    /* handle joints */
    /** for LEFT JOIN */
    const PRIORITY_LOW = 1;
    /** for INNER JOIN */
    const PRIORITY_NORMAL = 2;
    /** for RIGHT JOIN */
    const PRIORITY_HIGH = 4;

    private $joints;
    /**
     * omit $link_to or $link_from for NATURAL JOIN ...
     * ... i.e join on identical columns names.
     * Caution: this can be unpredictable!
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
    public function if( $what, $checks_table_columns = true ){
        if( $what instanceof check\_PDONoSQLCheck ||
            $what instanceof check\_PDONoSQLBag )
            if($checks_table_columns) $this->where = $what;
            else $this->having = $what;
        else throw new \Exception("expecting a pdonosql\check\_PDONoSQLCheck or a pdonosql\check\_PDONoSQLBag", 1);

        return $this;
    }

    /* handle groups by */
    private $group = '';
    public function groupBy( string ...$columns ) {
        $this->group = ' GROUP BY '.join( ', ',
            array_map(function( $col ){ return utils\Utils::noInject( $col ); }, $columns) );
        return $this;
    }

    /* handle order by */
    private $order = '';
    public function orderBy( string ...$columns ) {
        $this->order = ' ORDER BY '.join(', ',
            array_map(function( $col ){ return utils\Utils::noInject( $col ); }, $columns) );
        return $this;
    }

    /* handle order by */
    private $limit = '';
    public function takeOnly( int $row_count, int $from_row=0 ) {
        $this->limit = ' LIMIT '.$from_row.', '.$row_count;
        return $this;
    }


    /* CRUD ACTIONS */
    public function create( array $data ): int /* number of rows created */ {
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

            return $this->connection->exec($query);
        }catch(\Exception $e){
            $this->exception = $e;
            return 0;
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
    public function read( string ...$columns ): array /* read data in assoc-array */ {
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

    public function update( array $data, $data_check = null ): int /* number of rows updated */ {
        if( ! is_null($data_check) )
            if( !($data_check instanceof check\_PDONoSQLCheck ||
                $data_check instanceof check\_PDONoSQLBag) )
                throw new \Exception("expecting a pdonosql\check\_PDONoSQLCheck or a pdonosql\check\_PDONoSQLBag", 1);

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

    public function delete(){
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


    public function getTables(){
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

    public function getFields(){
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
