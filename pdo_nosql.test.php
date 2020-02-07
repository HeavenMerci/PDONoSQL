<?php

/**
 * @author HeavenMercy
 *
 * these tests depend on the database in the file 'test_db.sql'.
 * You need to load that file in your SQL DBMS to create the database.
 */

include_once 'pdonosql/utils/autoload_class.php';


const HOST=  'localhost';
const USRNAME = 'root';
const PASSWD = 'root';

const DBNAME = 'test';



$connection = new PDO('mysql:host='.HOST.';dbname='.DBNAME, USRNAME, PASSWD);
$nosql = new pdonosql\PDONoSQL( $connection );


// ---------------------------------------------------------------------------------


/*  to set the test to execute
- CREATE:               1
- READ:                 2
- UPDATE:               3
- DELETE:               4
- GET TABLES LIST:      5
- GET TABLE FIELDS:     6
----------------------------- */
const TEST_QUERY_ID = 6;


/* CREATE TEST
----------------------------- */
if( TEST_QUERY_ID == 1 ){
    $result = $nosql
        ->in( 'employee' )
        ->if( new pdonosql\condition\bag\AndBag(
            new pdonosql\condition\check\Greater('salary', 49999),
            new pdonosql\condition\check\Equal('branch_id', 4) ) )
        ->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1994-02-27',
            'sex' => 'M',
            'salary' => 50000,
            'branch_id' => 4
        ]);
}

else

/* READ TEST
----------------------------- */
if( TEST_QUERY_ID == 2 ){
    $result = "number of employees per branch = ".pdonosql\utils\Utils::toJSON(
        $nosql->clear()
            ->in( 'employee' )
            ->andIn( 'branch' )
            ->groupBy( 'branch_id' )
            //->if( new pdonosql\check\Greater('emp_count', 2), false )
            ->read( 'branch_name', pdonosql\utils\Utils::numberOf('emp_id', 'emp_count') )
    , true);


    $result .= "\n\nsome branch managers informations = ".pdonosql\utils\Utils::toJSON(
        $nosql->clear()
            ->in( 'employee' )
            ->andIn( 'branch', 'mgr_id', 'emp_id' )
            ->orderBy( pdonosql\utils\Utils::descending('salary') )
            ->if( new pdonosql\condition\bag\OrBag(
                new pdonosql\condition\check\Equal('last_name', 'Mercy'),
                new pdonosql\condition\check\Equal('last_name', 'Scott') ) )
            ->read( pdonosql\utils\Utils::concat(['first_name', 'last_name'], "mgr_name"),
                'salary', 'branch_name' )
    , true);


    $result .= "\n\nlist of employees = ".pdonosql\utils\Utils::toJSON(
        $nosql->clear()
            ->in( 'employee' )
            ->orderBy( 'birth_date' )
            ->takeOnly( 5 )
            ->read( 'emp_id', 'first_name', 'last_name', 'birth_date' )
    , true);
}

else

/* UPDATE TEST
----------------------------- */
if( TEST_QUERY_ID == 3 ){
    $result = $nosql
        ->in( 'employee' )
        ->if( new pdonosql\condition\check\Equal('emp_id', 110) )
        ->update( ['super_id' => 109], new pdonosql\condition\check\NotEqual('super_id', 110) );
}

else

/* DELETE TEST
----------------------------- */
if( TEST_QUERY_ID == 4 ){
    $nosql = new pdonosql\PDONoSQL(
        new PDO('mysql:host=localhost;dbname=sftest', 'root', 'root') );

    $result = $nosql
        ->in( 'todo' )
        ->if( new pdonosql\condition\check\Equal('id', 7) )
        ->delete();
}

else

/* GET TABLES TEST
----------------------------- */
if( TEST_QUERY_ID == 5 ){
    $result = pdonosql\utils\Utils::toJSON(
        $nosql->clear()
            ->getTables()
    , true);
}

else

/* GET TABLE FIELDS TEST
----------------------------- */
if( TEST_QUERY_ID == 6 ){
    $result = pdonosql\utils\Utils::toJSON(
        $nosql->clear()
            ->in("branch")
            ->getFields()
    , true);
}



if( $nosql->isOK() ){
    if( isset($result) ) echo $result;
}else echo 'Error: '.$nosql->getException()->getMessage();
