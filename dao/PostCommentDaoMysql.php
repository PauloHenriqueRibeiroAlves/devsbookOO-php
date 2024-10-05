<?php //ARQUIVO 2

require_once 'models/PostComment.php';
require_once 'dao/UserDaoMysql.php';

class PostCommentDaoMysql implements PostCommentDao {
    private $pdo;

    public function __construct(PDO $driver) {
        $this -> pdo = $driver;
    }
    
    public function getComments($id_post){
        $array = [];

        //pegando os comentário do banco de dados
        $sql = $this -> pdo -> prepare("SELECT * FROM postcomments
        WHERE id_post = :id_post");
        //mudando os valores no banco de dados
        $sql -> bindValue(':id_post', $id_post);
        $sql -> execute();
        //verificando se há algum comentário
        if($sql->rowCount() > 0) {
            //pegando os dados e colocando em uma variavel só
            $data = $sql -> fetchAll(PDO::FETCH_ASSOC);
            //usando a função para pegar as informações do usuário
            $userDao = new UserDaoMysql($this -> pdo);
            //transformando os comentários em um objetos anmtes de adicionar na tela
            foreach($data as $item) {
                //montando as informações de cada post
                $commentItem = new PostComment();
                $commentItem -> id = $item['id'];
                $commentItem -> id_post = $item['id_post'];
                $commentItem -> id_user = $item['id_user'];
                $commentItem -> body = $item['body'];
                $commentItem -> created_at = $item['created_at'];
                //função que vai procurar o usuário do comentário
                $commentItem -> user = $userDao -> findById($item['id_user']);

                $array[] = $commentItem;
            }
        }

        return $array;
    }

    public function addComment(PostComment $pc){
        //inserindo comnetários no banco de dados
        $sql = $this -> pdo -> prepare("INSERT INTO postcomments
        (id_post, id_user, body, created_at) VALUES 
        (:id_post, :id_user, :body, :created_at)");

        $sql -> bindValue(':id_post', $pc -> id_post);
        $sql -> bindValue(':id_user', $pc -> id_user);
        $sql -> bindValue(':body', $pc -> body);
        $sql -> bindValue(':created_at', $pc -> created_at);
        $sql -> execute();
    }

    //excluindo comentário do post
    public function deleteFromPost($id_post) {
        $sql = $this->pdo->prepare("DELETE FROM postcomments WHERE id_post = :id_post");
        $sql->bindValue(':id_post', $id_post);
        $sql->execute();
    }
    
}