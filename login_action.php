<?php
require_once 'config.php';
require_once 'models/Auth.php';

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');

if($email && $password) {
    //para fazer a verificação precisa instanciar o $auth
    $auth = new Auth($pdo, $base);

    if($auth -> validateLogin($email, $password)) {
        header("Location: ".$base);
        exit;
    }
}
//colocando a message, de erro na cessão
$_SESSION['flash'] = 'Email e/ou senha errados.';
 header ("Location: ".$base."/login.php");
 exit;