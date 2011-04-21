<?php

require_once 'Nette/Debug.php';
require_once '../dibi/dibi.php';

/* User-defined modifiers */
require_once dirname(__FILE__) . '/libs/UserModifiers.php';

$conn = dibi::connect(array(
    'driver'   => 'postgre',
    'host'     => 'localhost',
    'username' => 'test',
    'password' => 'test',
    'database' => 'test',
));




echo '<h1>MaxModifier</h1>';
$conn->registerModifier('max', new MaxModifier); // dibi::registerModifier()
dibi::test('SELECT %{max}', 'id');




echo '<h1>ModModifier with two arguments</h1>';
$conn->registerModifier('mod', new ModModifier);
dibi::test('SELECT %{mod}', 31, 8, 'FROM table1');




echo '<h1>PostgreSQL INT arrays</h1>';
/*
	CREATE TABLE table1 (
	  "id"      INTEGER,
	  "array1d" INTEGER[],
	  "array2d" INTEGER[][]
	);
*/

$conn->registerModifier('ari', new PgArrayIntModifier);
$result = $conn->query('SELECT id, array1d, array2d FROM table1');

echo '<h2>Manual type cast</h2>';
$result->setType('array2d', $conn->getModifier('ari')); // dibi::getModifier()
Debug::dump($result->fetch());
/*
	DibiRow(3) {
	   "id" => NULL
	   "array1d" => "{1,2,NULL,3}" (12)
	   "array2d" => array(2) [
	      0 => array(3) [
	         0 => 1
	         1 => NULL
	         2 => NULL
	      ]
	      1 => array(3) [
	         0 => 4
	         1 => NULL
	         2 => -3
	      ]
	   ]
	}
*/


echo '<h2>Data type detection</h2>';
$result->detectTypes();
Debug::dump($result->fetch());
/*
		DibiRow(3) {
		   "id" => 1
		   "array1d" => array(4) [
		      0 => 1
		      1 => 2
		      2 => NULL
		      3 => 3
		   ]
		   "array2d" => array(2) [
		      0 => array(3) [
		         0 => 1
		         1 => 2
		         2 => 3
		      ]
		      1 => array(3) [
		         0 => 4
		         1 => 5
		         2 => 6
		      ]
		   ]
		}
*/


echo '<h1>INSERT and UPDATE modifiers</h2>';
$data = array(
        'id%i'          => 1,
        'array1d%{ari}' => array(1, 2, 3, 4, 5),
        'array2d%{ari}' => array(array(1,2), array(3,4), array(NULL,NULL)),
);

dibi::test('INSERT INTO table1 %v', $data);
dibi::test('UPDATE table1 SET array1d = %{ari}', array(1,2,NULL,3), 'WHERE id = %i', 1);
