<?php //ARQUIV 2

require_once 'models/PostLike.php';

class PostLikeDaoMysql implements PostLikeDao {
    private $pdo;

    public function __construct(PDO $driver) {
        $this -> pdo = $driver;
    }
    
    public function getLikeCount($id_post) {
        //verificando no banco de dados
        $sql = $this -> pdo -> prepare("SELECT COUNT(*) as c FROM postlikes
        WHERE id_post = :id_post");
        //função que vai mudar o valor no banco
        $sql -> bindValue(':id_post', $id_post);
        $sql -> execute();

        $data = $sql -> fetch();
        return $data['c'];
    }
    
    public function isLiked($id_post, $id_user) {
        $sql = $this -> pdo -> prepare("SELECT * FROM postlikes
        WHERE id_post = :id_post and id_user = :id_user");

        $sql -> bindValue(':id_user', $id_user);
        $sql -> bindValue(':id_post', $id_post);
        $sql -> execute();

        //verificando se o usuário ja deu like no post
        if($sql -> rowCount() > 0) {
            return true;
        }else {
            return false;
        }
    }
    
    public function likeToggle($id_post, $id_user) {
        if($this -> isLiked($id_post, $id_user)) {
            //tirando o like
            $sql = $this -> pdo -> prepare("DELETE FROM postlikes
            WHERE id_post = :id_post AND id_user = :id_user");
        }else {
            //inserindo o like caso não tenha like
            $sql = $this -> pdo -> prepare("INSERT INTO postlikes
            (id_post, id_user, created_at) VALUES 
            (:id_post, :id_user, NOW())");
        }
        //mudando os valores
        $sql -> bindValue(':id_post', $id_post);
        $sql -> bindValue(':id_user', $id_user);
        $sql -> execute();
    }

    //excluindo likes do post
    public function deleteFromPost($id_post) {
        $sql = $this->pdo->prepare("DELETE FROM postlikes WHERE id_post = :id_post");
        $sql->bindValue(':id_post', $id_post);
        $sql->execute();
    }
}