<h1>Hello World PHP</h1>
// frsd.kjgnvsafklng;lkfdsngkldfgnbkldgnblkndgs;lbnfsg;ln
<?php
function fooFunc(&$a)
{
	$a %= 10;
}

for ($i = 1; $i <= 5; ++$i)
{
	echo "<p>This is paragraph $i</p>\n";
	$s1 = "456";
	$i1 = 789;
}
$a = array();
$a[] = 764;
$a[5] = "jhdsbjhsd";
$a["foo"] = "bar";
//$a["a"] = array();
//echo "<pre>";
//print_r($a);
//echo "</pre>";

foreach ($a as $key => $value)
{
	echo "Key: $key value: $value<br>\n";
}

$variable = 42;
fooFunc($variable);
echo $variable;

$con = mysqli_connect("localhost", "root", "", "testdb") or die(mysqli_error());

$q = "select bar, baz from foo";
$res = mysqli_query($con, $q);
if ($res)
{
	while ($row = mysqli_fetch_assoc($res))
	{
		echo "bar: " . $row["bar"] . " baz: ". $row["baz"] . "<br>\n";
	}
}


mysqli_close($con);

/*
c:\xampp\htdocs
Delete everything in there
Put index.php in it to make it serve the file at http://localhost/
###################

C:\Users\Admin>color A

C:\Users\Admin>cd\xampp\mysql\bin

C:\xampp\mysql\bin>mysql -uroot
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 8
Server version: 10.4.27-MariaDB mariadb.org binary distribution

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]> create database testdb;
Query OK, 1 row affected (0.001 sec)

MariaDB [(none)]> use testdb;
Database changed
MariaDB [testdb]> create table foo (bar int primary key, baz varchar(255));
Query OK, 0 rows affected (0.005 sec)

MariaDB [testdb]> insert into foo values (1, 'foo'), (2, 'bar'), (7, 'baz');
Query OK, 3 rows affected (0.023 sec)
Records: 3  Duplicates: 0  Warnings: 0

MariaDB [testdb]> select * from foo;
+-----+------+
| bar | baz  |
+-----+------+
|   1 | foo  |
|   2 | bar  |
|   7 | baz  |
+-----+------+
3 rows in set (0.001 sec)

MariaDB [testdb]>

*/

?>