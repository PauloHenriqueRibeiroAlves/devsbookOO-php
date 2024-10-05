<?php //ARQUIVO 2
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysql.php';

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();//usando a verificação do token

$id = filter_input(INPUT_GET, 'id');//pegando o que foi digitado no input

if($id) {
    $userRelationDao = new UserRelationDaoMysql($pdo);    
    $userDao = new UserDaoMysql($pdo);//pegando a função de verificar se o usuário existe
    
    if($userDao->findById($id)) {//verificando se o usuário existe
        $relation = new UserRelation();
        $relation->user_from = $userInfo->id;
        $relation->user_to = $id;
        
        if($userRelationDao->isFollowing($userInfo->id, $id)) {   //verificando se ja sigo o usuário         
            $userRelationDao->delete($relation);//Deixar de segui
        }else {            
            $userRelationDao->insert($relation);//seguir o usuário
        }

        header("Location: perfil.php?id=".$id);
        exit;
    }
}

header("Location: ".$base);
exit;