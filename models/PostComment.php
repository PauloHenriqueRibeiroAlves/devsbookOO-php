<?php //ARQUIVO 2
    class PostComment {
        public $id;
        public $id_post;
        public $id_user;
        public $created_at;
        public $body;
        //variavel que vou adicionada pois dava erro sem ela
        public $user;
    }

    //interface que vai ter as ações que o Dao vai ter
    interface PostCommentDao {
        //função que vai pegar os comentários
        public function getComments($id_post);
        //função que vai adicionar um comentário no post
        public function addComment(PostComment $pc);
    }

?>