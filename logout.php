<?php
require 'config.php';

$_SESSION['token'] = '';//retirando o token para sair da seção
header("Location: ".$base);
exit;