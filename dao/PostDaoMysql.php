<?php //ARQUIVO 2
require_once 'models/Post.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysql.php';
require_once 'dao/PostLikeDaoMysql.php';
require_once 'dao/PostCommentDaoMysql.php';

class PostDaoMysql implements PostDAO {
    private $pdo;

    public function __construct(PDO $driver) {
        $this -> pdo = $driver;
    }

    //função que vai inserir o  post
    public function insert(Post $p) {
        $sql = $this -> pdo -> prepare('INSERT INTO posts (
            id_user, type, created_at, body
        ) VALUES (
            :id_user, :type, :created_at, :body
        )');
    //mudando os valores dos campos
        $sql -> bindValue(':id_user', $p -> id_user);
        $sql -> bindValue(':type', $p -> type);
        $sql -> bindValue(':created_at', $p -> created_at);
        $sql -> bindValue(':body', $p -> body);
        $sql -> execute();
    }

    //função para deletar o post
    public function delete($id, $id_user) {
        //excluindo curtida e comentários no banco de dados do post excluido
        $postLikeDao = new PostLikeDaoMysql($this->pdo);
        $postCommentDao = new PostCommentDaoMysql($this->pdo);

        //1-fase - Saber se o post existe e o tipo do post
        $sql = $this->pdo->prepare("SELECT * FROM posts 
        WHERE id = :id AND id_user = :id_user");
        $sql->bindValue(':id', $id);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        //verificando se achou algum registro
        if($sql->rowCount() > 0) {
            //pegando o próprio post
            $post = $sql->fetch(PDO::FETCH_ASSOC);

            //2-fase - Deletar likes e comentários do post
            $postLikeDao->deleteFromPost($id);
            $postCommentDao->deleteFromPost($id);

            //3-fase - deletar o post foto com o type('photo')
            if($post['type'] === 'photo') {
                $img = 'media/uploads/'.$post['body'];
                //verificando se o arquivo existe
                if(file_exists($img)) {
                    unlink($img);
                }
            }

            //4-fase - deletando o post
            $sql = $this->pdo->prepare("DELETE FROM posts 
            WHERE id = :id AND id_user = :id_user");
            $sql->bindValue(':id', $id);
            $sql->bindValue(':id_user', $id_user);
            $sql->execute();
        }        
    }

    //função que ai pegar o feed expecifico do usuário
    public function getUserFeed($id_user, $page = 1) {
        $array = ['feed'=>[]];

        //limitando a paginação em 5 posts
        $perPage = 4;

        $offset = ($page - 1) * $perPage;

        //pegar os posts dos usurios que eu sigo ordenado pela data
        $sql = $this -> pdo -> prepare("SELECT * FROM posts 
        WHERE id_user = :id_user ORDER BY created_at DESC LIMIT $offset,$perPage");
        $sql -> bindValue(':id_user', $id_user);
        $sql -> execute();

        if($sql -> rowCount() > 0) {
            $data = $sql -> fetchAll(PDO::FETCH_ASSOC);

            //transformar os posts em objetos antes de serem exibidos
            $array['feed'] = $this -> _postListToObject($data, $id_user);
        }

        $sql = $this -> pdo -> prepare("SELECT COUNT(*) as c FROM posts 
        WHERE id_user = :id_user");
        $sql -> bindValue(':id_user', $id_user);
        $sql -> execute();

        //pegando o resultado da busca da query
        $totalData = $sql->fetch();
        //pegando o c da procura da query
        $total = $totalData['c'];

        //exibindo as paginas que tenho arredondando para cima
        $array['pages'] = ceil($total / $perPage);

        //variavél que vai conter a página atual
        $array['currentPage'] = $page;

        return $array;
    }

    //função que vai pegar os posts dos usuarios aapartir do feed
    public function getHomeFeed($id_user, $page = 1) {
        $array = [];

        //limitando a paginação em 5 posts
        $perPage = 4;

        $offset = ($page - 1) * $perPage;

        //listar usuários que eu sigo
        $urDao = new UserRelationDaoMysql($this -> pdo);
        $userList = $urDao -> getFollowing($id_user);
        $userList[] = $id_user;

        //pegar os posts dos usurios que eu sigo ordenado pela data
        $sql = $this -> pdo -> query("SELECT * FROM posts 
        WHERE id_user IN (".implode(',', $userList).") 
        ORDER BY created_at DESC, id DESC LIMIT $offset,$perPage");

        if($sql -> rowCount() > 0) {
            $data = $sql -> fetchAll(PDO::FETCH_ASSOC);

            //transformar os posts em objetos antes de serem exibidos
            $array['feed'] = $this -> _postListToObject($data, $id_user);
        }
        
        //pegar o total de poste para fazer a troca da paginação
        $sql = $this -> pdo -> query("SELECT COUNT(*) as c FROM posts 
        WHERE id_user IN (".implode(',', $userList).")");

        //pegando o resultado da busca da query
        $totalData = $sql->fetch();
        //pegando o c da procura da query
        $total = $totalData['c'];
        //exibindo as paginas que tenho arredondando para cima
        $array['pages'] = ceil($total / $perPage);

        //variavél que vai conter a página atual
        $array['currentPage'] = $page;

        return $array;
    }

    public function getPhotosFrom($id_user) {
        $array = [];

        $sql = $this -> pdo -> prepare("SELECT * FROM posts
        WHERE id_user = :id_user AND type = 'photo'
        ORDER BY created_at DESC");
        $sql -> bindValue(':id_user', $id_user);
        $sql -> execute();

        if($sql -> rowCount() > 0) {
            $data = $sql -> fetchAll(PDO::FETCH_ASSOC);

            //transformar os posts em objetos antes de serem exibidos
            $array = $this -> _postListToObject($data, $id_user);
        }

        return $array;
    }

    //função auxiliar que vai tranformar em objetos
    private function _postListToObject($post_list, $id_user) {
        //lista de posts
        $post = [];
        $userDao = new UserDaoMysql($this -> pdo);
        //função que vai dar o like
        $postLikeDao = new PostLikeDaoMysql($this -> pdo);
        //funçlõ que vai fazer o comentários nos posts
        $postCommentDao = new PostCommentDaoMysql($this -> pdo);

        //função que vai receber todas as inforções da tabela Post do bando de dados e organizar para exibir
        foreach($post_list as $post_item) {
            $newPost = new Post();
            $newPost -> id = $post_item['id'];
            $newPost -> type = $post_item['type'];
            $newPost -> created_at = $post_item['created_at'];
            $newPost -> body = $post_item['body'];
            $newPost -> mine = false; //identificadp se o post é meu

            //condição para saber quando o post é meu
            if($post_item['id_user'] == $id_user) {
                $newPost -> mine = true;
            }

            //pegar informações do usuário, para mostrar o post dele
            $newPost -> user = $userDao -> findbyId($post_item['id_user']);

            //informações sobre os likes dos posts
            $newPost -> likeCount =  $postLikeDao -> getLikeCount($newPost -> id);//quantidade de likes
            $newPost -> liked = $postLikeDao -> isLiked($newPost -> id, $id_user);//se eu curte um post ou não

            //informações sobre os comentários dos posts
            $newPost -> comments = $postCommentDao -> getComments($newPost -> id);

            //função que vai inserir as informções dentro do array vazio
            $post[] = $newPost;
        }

        return $post;
    }
}