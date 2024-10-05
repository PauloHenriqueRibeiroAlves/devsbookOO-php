<?php //ARQUIVO 2
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostDaoMysql.php';//deu errado

$auth = new Auth($pdo, $base);
$userInfo = $auth -> checkToken();//usando a verificação do token

//variável vazia ode vai ficar o comentário
$array = ['error' => ''];

$postDao = new PostDaoMysql($pdo);

//copndição se foi enviada a imagem ou não
if(isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
    $photo = $_FILES['photo'];

    //condição para saber o tipo da foto que vamos receber
    if(in_array($photo['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {

        //pgado o tamanho original da foto que foi enviada
        list($widthOrig, $heightOrig) = getimagesize($photo['tmp_name']);

        //tirando o raio da imagem
        $ratio = $widthOrig / $heightOrig;

        //pegando a imagem
        $newWidth = $maxWidth;
        $newHeight = $maxHeight;

        //recalculando o raio da foto
        $ratioMax = $maxWidth / $newHeight;

        //fazendo comparação dos dois
        if($ratioMax > $ratio) {
            $newWidth = $newHeight * $ratio;
        }else {
            $newHeight = $newWidth / $ratio; 
        }

        /*//cortando a imagem
        $newWidth = $maxWidth;
        $newHeight = $newWidth / $ratio;

        //recalculando a altura proporcional
        if($newHeight < $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $ratio;
        }*/

        //criando a nova imagem
        $finalImage = imagecreatetruecolor($newWidth, $newHeight);

        //pegando a imagem original
        switch($photo['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($photo['tmp_name']);
            break;
            case 'image/png':
                $image = imagecreatefrompng($photo['tmp_name']);
            break;
        }

        //copiar uma dentro da outra
        imagecopyresampled(
            $finalImage, $image,
            0, 0, 0, 0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );

        //gerando nome aleatório para a imagem
        $photoName = md5(time().rand(0, 9999)).'.jpg';

        //salvando a nova foto
        imagejpeg($finalImage, 'media/uploads/'.$photoName);

        //construindo a imagem antes de salvar
        $newPost = new Post();
        $newPost -> id_user = $userInfo ->id;
        $newPost -> type = 'photo';
        $newPost -> created_at = date('Y-m-d H:i:s');
        $newPost -> body = $photoName;

        //inserindo no banco de dados
        $postDao -> insert($newPost);

    }else {
        $array['error'] = 'Aquivo não suportado (jpg ou png).';
    }
}else {
    $array['error'] = 'Nenhuma imagem enviada';
}


//para onde vai o comentário que recebeu
header("Content-Type: application/json");
echo json_encode($array);
exit;