<?php //ARQUIVO 2
class Post {
    public $id;
    public $id_user;
    public $type;//text ou foto
    public $created_at;
    public $body;
    //prorpiedades que deram erro porque não estava declarada
    public $mine;
    public $user;
    public $likeCount;
    public $liked;
    public $comments;
}
//interface para a criação do DAO
interface PostDAO {
    public function insert(Post $p);
    //função para deletar o post
    public function delete($id, $id_user);
    //função que vai pegar os posts dos usuarios aapartir do feed
    public function getHomeFeed($id_user);
    //função que vai pegar o feed expecifico do usuário
    public function getUserFeed($id_user);
    public function getPhotosFrom($id_user);
}