<?php //ARQUIVO 2
require_once 'models/UserRelation.php';// Para pegar so uma vez

class UserRelationDaoMysql implements UserRelationDAO {
    private $pdo;

    public function __construct(PDO $driver) {
        $this -> pdo = $driver;
    }

    //função para inserir um dados no usurios
    public function insert(UserRelation $u) {
        //procurando relações no banco de dados
        $sql = $this->pdo->prepare("INSERT INTO  userrelations 
        (user_from, user_to) VALUES (:user_from, :user_to)");
        //alterando os valores da relação de cada usuário
        $sql -> bindValue(':user_from', $u->user_from);
        $sql -> bindValue(':user_to', $u->user_to);
        $sql -> execute();
    }

    //função para deletar a relação entre os usuários
    public function delete(UserRelation $u) {
        //procurando relações no banco de dados para excluir
        $sql = $this->pdo->prepare("DELETE FROM userrelations
        where user_from = :user_from AND user_to = :user_to");
        //alterando os valores da relação de cada usuário
        $sql->bindValue(':user_from', $u->user_from);
        $sql->bindValue(':user_to', $u->user_to);
        $sql->execute();
    }
    //função que vai pegar as informações da relação de cada usuário
    public function getFollowing($id) {
        $users = [];

        $sql = $this->pdo->prepare("SELECT user_to FROM userrelations
        WHERE user_from = :user_from");

        $sql->bindValue(':user_from', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $data = $sql -> fetchAll();
            foreach($data as $item) {
                $users[] = $item['user_to'];
            }
        }

        return $users;
    }

    public function getFollowers($id) {
        $users = [];

        $sql = $this->pdo->prepare("SELECT user_from FROM userrelations
        WHERE user_to = :user_to");

        $sql->bindValue(':user_to', $id);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $data = $sql->fetchAll();
            foreach($data as $item) {
                $users[] = $item['user_from'];
            }
        }

        return $users;
    }

    //função que vai seguir o usuário
    public function isFollowing($id1, $id2) {
        //consultando o banco de dados para ver se o usuário segue o outro
        $sql = $this->pdo->prepare("SELECT * FROM userrelations WHERE 
        user_from = :user_from AND user_to = :user_to");
        //alterando os valores denro do banco de dados
        $sql->bindValue(':user_from', $id1);
        $sql->bindValue(':user_to', $id2);
        $sql->execute();

        if($sql -> rowCount() > 0) {
            return true;
        }else {
            return false;
        }
    }
}