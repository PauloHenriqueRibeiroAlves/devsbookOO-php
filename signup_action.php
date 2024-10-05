<?php //ARQUIVO 2
require 'config.php';
require 'models/Auth.php';

$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');
$birthdate = filter_input(INPUT_POST, 'birthdate');

    if($name && $email && $password && $birthdate) {
        //para fazer a verificação precisa instanciar o $auth
        $auth = new Auth($pdo, $base);

        //tansformando a data de nascimento em um array
        $birthdate = explode('/', $birthdate);
        //verificando a data de nascimento
        if(count($birthdate) != 3) {
            //colocando a messagem, de erro na cessão
            $_SESSION['flash'] = 'Data de nascimento invalida.';
            header ("Location: ".$base."/signup.php");
            exit;
        }

        //pegando a data internacional
        $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
        //validando se a data é real como dd/mm/aaaa
        if(strtotime($birthdate) === false) {
            //colocando a message, de erro na cessão
            $_SESSION['flash'] = 'Data de nascimento invalida.';
            header ("Location: ".$base."/signup.php");
            exit;
        }
        //validando se o email existe
        if($auth -> emailExists($email) === false) {
            //mandando as informações para ser registradas
            $auth -> registerUser($name, $email, $password, $birthdate );
            //mandando o usuário para a pagina inicial
            header ("Location: ".$base);
            exit;
        }else {
            //colocando a message, de erro na cessão
            $_SESSION['flash'] = 'E-amil já cadastrado.';
            header ("Location: ".$base."/signup.php");
            exit;
        }
    }
//colocando a message, de erro na cessão
$_SESSION['flash'] = 'Campos não enviados.';
 header ("Location: ".$base."/signup.php");
 exit;