<?php
session_start();
$base = 'http://localhost:8080/aulaphp9/devsbookoo';

$db_name = 'devsbook';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

$maxWidth = 800;//variavél que vai conter os tamhanhos permitidos
$maxHeight = 800;

$pdo = new PDO("mysql:dbname=".$db_name.";host=".$db_host, $db_user, $db_pass);