<?php

include_once 'pdonosql/utils/autoload_class.php';

$connection = new PDO('mysql:host=localhost;dbname=test', 'root', 'root');
$nosql = new pdonosql\PDONoSQL( $connection );




/* CREATE TEST
----------------------------- */
// $result = $nosql
//     ->in( 'employee' )
//     ->if( new pdonosql\check\AndBag(
//         new pdonosql\check\Greater('salary', 49999),
//         new pdonosql\check\Equal('branch_id', 4) ))
//     ->create([
//         'first_name' => 'John',
//         'last_name' => 'Doe',
//         'birth_date' => '1994-02-27',
//         'sex' => 'M',
//         'salary' => 50000,
//         'branch_id' => 4
//     ]);



/* READ TEST
----------------------------- */
// $result = "number of employees per branch = ".pdonosql\utils\Utils::toJSON(

// $nosql->clear()
//     ->in( 'employee' )
//     ->andIn( 'branch' )
//     ->groupBy( 'branch_id' )
//     //->if( new pdonosql\check\Greater('emp_count', 2), false )
//     ->read( 'branch_name', pdonosql\utils\Utils::numberOf('emp_id', 'emp_count') )

// , true);

// $result .= "\n\nsome branch managers informations = ".pdonosql\utils\Utils::toJSON(

//     $nosql->clear()
//         ->in( 'employee' )
//         ->andIn( 'branch', 'mgr_id', 'emp_id' )
//         ->orderBy( pdonosql\utils\Utils::descending('salary') )
//         ->if( new pdonosql\check\OrBag(
//             new pdonosql\check\Equal('last_name', 'Mercy'),
//             new pdonosql\check\Equal('last_name', 'Scott') ) )
//         ->read( pdonosql\utils\Utils::concat(['first_name', 'last_name'], "mgr_name"),
//             'salary', 'branch_name' )

// , true);


// $result .= "\n\nlist of employees = ".pdonosql\utils\Utils::toJSON(

//     $nosql->clear()
//         ->in( 'employee' )
//         ->orderBy( 'birth_date' )
//         ->takeOnly( 5 )
//         ->read( 'emp_id', 'first_name', 'last_name', 'birth_date' )

// , true);



/* UPDATE TEST
----------------------------- */
// $result = $nosql
//     ->in( 'employee' )
//     ->if( new pdonosql\check\Equal('emp_id', 110) )
//     ->update( ['super_id' => 109], new pdonosql\check\NotEqual('super_id', 110) );



/* DELETE TEST
----------------------------- */
// $nosql = new pdonosql\PDONoSQL(
//     new PDO('mysql:host=localhost;dbname=sftest', 'root', 'root') );

// $result = $nosql
//     ->in( 'todo' )
//     ->if( new pdonosql\check\Equal('id', 7) )
//     ->delete();





$result = pdonosql\utils\Utils::toJSON(

$nosql->clear()
    ->getTables()

);


if( $nosql->isOK() ) echo $result;
else echo 'Error: '.$nosql->getException()->getMessage();
