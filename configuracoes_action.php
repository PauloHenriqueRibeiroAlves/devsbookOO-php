<?php //ARQUIVO 2
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/UserDaoMysql.php';//deu errado também, junto com o ajax

$auth = new Auth($pdo, $base);
$userInfo = $auth -> checkToken();//usando a verificação do token

$userDao = new UserDaoMysql($pdo);
//pegando todos os valores dos campos
$name = filter_input(INPUT_POST, 'name');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$birthdate = filter_input(INPUT_POST, 'birthdate');
$city = filter_input(INPUT_POST, 'city');
$work = filter_input(INPUT_POST, 'work');
$password = filter_input(INPUT_POST, 'password');
$password_confirmation = filter_input(INPUT_POST, 'password_confirmation');

if($name && $email) {
    //pegando valores que não precisando de verificação
    $userInfo->name = $name;
    $userInfo->city = $city;
    $userInfo->work = $work;

    //verificando se email for diferente do campo digitado
    if($userInfo->email != $email) {
        //verificando se email for diferente do email do banco dde dados
        if($userDao->findByEmail($email) === false) {
            $userInfo->email = $email;
        }else {
            $_SESSION['flash'] = 'Email ja existe!';
            header ("Location: ".$base."/configuracoes.php");
            exit;
        }
    }

    //verificando se a data de nascimento é válida         
    $birthdate = explode('/', $birthdate);//tansformando a data de nascimento em um array
    if(count($birthdate) != 3) {
        //colocando a messagem, de erro na cessão
        $_SESSION['flash'] = 'Data de nascimento invalida.';
        header ("Location: ".$base."/configuracoes.php");
        exit;
    }
    
    //pegando a data internacional
    $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
    //validando se a data é real como dd/mm/aaaa
    if(strtotime($birthdate) === false) {
        //colocando a message, de erro na cessão
        $_SESSION['flash'] = 'Data de nascimento invalida.';
        header ("Location: ".$base."/configuracoes.php");
        exit;
    }
    
    //confimando a mudandça da data de nascimento
    $userInfo->birthdate = $birthdate;

    //verificando a senha
    if(!empty($password)) {
        if($password === $password_confirmation) {
            //gerando o hash para a senha do usuário
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userInfo->password = $hash;
        }else {
            $_SESSION['flash'] = 'As senhas não batem.';
            header ("Location: ".$base."/configuracoes.php");
            exit;
        }
    }

    //adicionando imagem de perfil ou avatar
    if(isset($_FILES['avatar']) && /*!empty($_FILES['avatar']['error'])ou*/ $_FILES['avatar']['error'] == '0') {
        $newAvatar = $_FILES['avatar'];

        //verificando o tipo da imgem que vai ser mandada
        if(in_array($newAvatar['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
            //definindo o tamanho que vai aceitar da imgem
            $avatarWidth = 200;
            $avatarHeight = 200;

            //pegando o tamanho real da imagem que foi mandada
            list($widthOrig, $heightOrig) = getimagesize($newAvatar['tmp_name']);
            $ratio = $widthOrig / $heightOrig;
            //gerando o novo tamanho que a imagem vai ter
            $newWidth = $avatarWidth;
            $newHeight = $newWidth / $ratio;
            //varificando se a imagem ficou menor que o que eu quero
            if($newHeight < $avatarHeight) {
                $newHeight = $avatarHeight;
                $newWidth = $newHeight * $ratio;
            }

            //chegando ao novo tamanho
            $x = $avatarWidth - $newWidth;
            $y = $avatarHeight - $newHeight;

            $x = $x<0 ? $x/2 : $x;
            $y = $y<0 ? $y/2 : $y;

            $finalImage = imagecreatetruecolor($avatarWidth, $avatarHeight);
            //carregando a imagem antes de mandar ela para o banco de dados
            switch($newAvatar['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($newAvatar['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newAvatar['tmp_name']);
                break;
            }
            //copiar uma imagem dentro da outra para fazer o corte
            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight,
                $widthOrig, $heightOrig
            );

            //salvando a imagem
            $avatarName = md5(time().rand(0,9999)).'.jpg';//OU .ROUND

            imagejpeg($finalImage, './media/avatars/'.$avatarName, 100);
            //inserindo a nova imagem
            $userInfo->avatar = $avatarName;
        }
    }

    //cover ou imagem de fundo
    if(isset($_FILES['cover']) && /*!empty($_FILES['cover']['error'])ou*/ $_FILES['cover']['error'] == '0') {
        $newCover = $_FILES['cover'];

        //verificando o tipo da imgem que vai ser mandada
        if(in_array($newCover['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
            //definindo o tamanho que vai aceitar da imgem
            $coverWidth = 850;
            $coverHeight = 313;

            //pegando o tamanho real da imagem que foi mandada
            list($widthOrig, $heightOrig) = getimagesize($newCover['tmp_name']);
            $ratio = $widthOrig / $heightOrig;
            //gerando o novo tamanho que a imagem vai ter
            $newWidth = $coverWidth;
            $newHeight = $newWidth / $ratio;
            //varificando se a imagem ficou menor que o que eu quero
            if($newHeight < $coverHeight) {
                $newHeight = $coverHeight;
                $newWidth = $newHeight * $ratio;
            }

            //chegando ao novo tamanho
            $x = $coverWidth - $newWidth;
            $y = $coverHeight - $newHeight;

            $x = $x<0 ? $x/2 : $x;
            $y = $y<0 ? $y/2 : $y;

            $finalImage = imagecreatetruecolor($coverWidth, $coverHeight);
            //carregando a imagem antes de mandar ela para o banco de dados
            switch($newCover['type']) {
                case 'image/jpg':
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($newCover['tmp_name']);
                break;
                case 'image/png':
                    $image = imagecreatefrompng($newCover['tmp_name']);
                break;
            }
            //copiar uma imagem dentro da outra para fazer o corte
            imagecopyresampled(
                $finalImage, $image,
                $x, $y, 0, 0,
                $newWidth, $newHeight,
                $widthOrig, $heightOrig
            );

            //salvando a imagem
            $coverName = md5(time().rand(0,9999)).'.jpg'; //ou .round

            imagejpeg($finalImage, './media/covers/'.$coverName, 100);
            //inserindo a nova imagem
            $userInfo->cover = $coverName;
        }
    }        
    
    //mudando as informações no bancoi de dados
    $userDao->update($userInfo);    
}

header("Location: ".$base."/configuracoes.php");
exit;
//tenho que corrigir esse código