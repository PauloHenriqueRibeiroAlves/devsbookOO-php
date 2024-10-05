<?php //ARQUIVO 2
class UserRelation {
    public $id;
    public $user_from;
    public $user_to;
}
//interface para a criação do DAO
interface UserRelationDAO {
    //função para inserir um dados no usurios
    public function insert(UserRelation $u);
    public function delete(UserRelation $u);
    //função que vai pegar as informações da relação de cada usuário
    public function getFollowing($id);
    public function getFollowers($id);
    public function isFollowing($id1, $id2);    
}