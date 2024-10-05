<?php //ARQUIVO 1
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostCommentDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth -> checkToken();//usando a verificação do token

$id = filter_input(INPUT_POST, 'id');//pegando o id de quem comentou
$txt = filter_input(INPUT_POST, 'txt');//pegando o texto do comentário
//variável vazia ode vai ficar o comentário
$array = [];

//condição se achou algum comentário
if($id && $txt) {
    //variável que vai pegar o post
    $postCommentDao = new PostCommentDaoMysql($pdo);
    //preenchendo as variáveis do comentário
    $newComment = new PostComment();
    $newComment -> id_post = $id;
    $newComment -> id_user = $userInfo -> id;
    $newComment -> body = $txt;
    $newComment -> created_at = date('Y-m-d H:i:s');
    //adicionando o comentário
    $postCommentDao -> addComment($newComment);

    //montando o array para retornar
    $array = [
        'error' => '',
        'link' => $base.'/perfil.php?id='.$userInfo -> id,
        'avatar' => $base.'/media/avatars/'.$userInfo -> avatar,
        'name' => $userInfo -> name,
        'body' => $txt
    ];
}

//para onde vai o comentário que recebeu
header("Content-Type: application/json");
echo json_encode($array);
exit;