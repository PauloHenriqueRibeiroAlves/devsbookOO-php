<?php //ARQUIVO 2
    class PostLike {
        public $id;
        public $id_post;
        public $id_user;
        public $created_at;
    }

    //interface que vai ter as ações que o Dao vai ter
    interface PostLikeDao {
        //contagem de like
        public function getLikeCount($id_post);
        //função que vai saber quem foi o usuário que deu like
        public function isLiked($id_post, $id_user);
        //função que vai dar o like
        public function likeToggle($id_post, $id_user);
    }

?>