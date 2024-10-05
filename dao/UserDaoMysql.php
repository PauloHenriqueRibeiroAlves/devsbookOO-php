<?php //ARQUIVO 2
require_once 'models/User.php';// Para pegar so uma vez
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/PostDaoMysql.php';

class UserDaoMysql implements UserDAO {
    private $pdo;

    public function __construct(PDO $driver) {
        $this -> pdo = $driver;
    }
    //função que vai geral o array de usuarios
    private function generateUser($array, $full = false) {
        $u = new User();
        //montando array
        $u -> id = $array['id'] ?? 0;
        $u -> email = $array['email'] ?? '';
        $u -> password = $array['password'] ?? '';
        $u -> name = $array['name'] ?? '';
        $u -> birthdate = $array['birthdate'] ?? '';
        $u -> city = $array['city'] ?? '';
        $u -> work = $array['work'] ?? '';
        $u -> avatar = $array['avatar'] ?? '';
        $u -> cover = $array['cover'] ?? '';
        $u -> token = $array['token'] ?? '';

        if($full) {
            $urDaoMysql = new UserRelationDaoMysql($this -> pdo);
            $postDaoMysql = new PostDaoMysql($this -> pdo);
            //followers - Quem segue o usuário
            $u->followers = $urDaoMysql->getFollowers($u->id);
            foreach($u->followers as $key => $follower_id) {//fazendo a troca dos objetos pela chave direto do key para que possa acessa diretamente o id do amigo que está seguindo
                $newUser = $this -> findById($follower_id);
                $u->followers[$key] = $newUser;
            }

            //follwing - Quem o usuário segue
            $u->following = $urDaoMysql->getFollowing($u->id);
            foreach($u->following as $key => $follower_id) {//fazendo a troca dos objetos pela chave direto do key para que possa acessa diretamente o id do amigo que está seguindo
                $newUser = $this -> findById($follower_id);
                $u->following[$key] = $newUser;
            }

            //fotos
            $u->photos = $postDaoMysql -> getPhotosFrom($u->id);
        }

        return $u;
    }
    //verificando o token no banco de dados efetivo
    public function findByToken($token) {
        if(!empty($token)) {
            $sql = $this -> pdo -> prepare("SELECT * FROM users WHERE token = :token");
            $sql -> bindValue(':token', $token);
            $sql -> execute();
            //se achou alguma coisa
            if($sql -> rowCount() > 0) {
                //pegando as informações do banco de dados
                $data = $sql -> fetch(PDO::FETCH_ASSOC);
                //monstando o array coom as informações dentro de $data
                $user = $this -> generateUser($data);
                return $user;
            }
        }
         return false;
    }

    //criando a função de verificar o email
    public function findByEmail($email) {
        if(!empty($email)) {
            $sql = $this -> pdo -> prepare("SELECT * FROM users WHERE email = :email");
            $sql -> bindValue(':email', $email);
            $sql -> execute();
            //se achou alguma coisa
            if($sql -> rowCount() > 0) {
                //pegando as informações do banco de dados
                $data = $sql -> fetch(PDO::FETCH_ASSOC);
                //monstando o array coom as informações dentro de $data
                $user = $this -> generateUser($data);
                return $user;
            }
        }
        return false;
    }

    public function findById($id, $full = false) {
        if(!empty($id)) {
            $sql = $this -> pdo -> prepare("SELECT * FROM users WHERE id = :id");
            $sql -> bindValue(':id', $id);
            $sql -> execute();
            //se achou alguma coisa
            if($sql -> rowCount() > 0) {
                //pegando as informações do banco de dados
                $data = $sql -> fetch(PDO::FETCH_ASSOC);
                //monstando o array coom as informações dentro de $data
                $user = $this -> generateUser($data, $full);
                return $user;
            }
        }
        return false;
    }

    //função para buscar o usuário pelo nome
    public function findByName($name) {
        //lista de usuários da busca
        $array = [];

        if(!empty($name)) {
            //fazendo a busca pelo nomme dentro do banco de dados
            $sql = $this -> pdo -> prepare("SELECT * FROM users WHERE name LIKE :name");
            //'%' para buscar quaçlquer coisas dentro de quaçlquer paramentro que digitar na busca
            $sql -> bindValue(':name', '%'.$name.'%');
            $sql -> execute();
            //se achou alguma coisa
            if($sql -> rowCount() > 0) {
                //pegando as informações do banco de dados
                $data = $sql -> fetchAll(PDO::FETCH_ASSOC);
                foreach($data as $item) {
                    $array[] = $this -> generateUser($item);
                }
            }
        }
        return $array;
    }

    //criando a função que vai atualizar o usuário no banco de dados
    public function update(User $u) {
        //os campos onde vou fazerr alteração
        $sql = $this -> pdo -> prepare("UPDATE users SET
            email = :email,
            password = :password,
            name = :name,
            birthdate = :birthdate,
            city = :city,
            work = :work,
            avatar = :avatar,
            cover = :cover,
            token = :token
            WHERE id = :id
        ");
        //muidando os valores do campo
        $sql -> bindValue(':email', $u -> email);
        $sql -> bindValue(':password', $u -> password);
        $sql -> bindValue(':name', $u -> name);
        $sql -> bindValue(':birthdate', $u -> birthdate);
        $sql -> bindValue(':city', $u -> city);
        $sql -> bindValue(':work', $u -> work);
        $sql -> bindValue(':avatar', $u -> avatar);
        $sql -> bindValue(':cover', $u -> cover);
        $sql -> bindValue(':token', $u -> token);
        $sql -> bindValue(':id', $u -> id);
        $sql -> execute();

        return true;
    }

    //criando a função que vai inserir os dados do uduário
    public function insert(User $u) {
        //criando a query, enserido os dados no banco de dados
        $sql = $this -> pdo -> prepare("INSERT INTO users (
            name, email, password, birthdate, token
        ) VALUES (
            :name, :email, :password, :birthdate, :token
        )");

        //alterando os valores dos campos
        $sql -> bindValue(':email', $u -> email);
        $sql -> bindValue(':name', $u -> name);
        $sql -> bindValue(':password', $u -> password);
        $sql -> bindValue(':birthdate', $u -> birthdate);
        $sql -> bindValue(':token', $u -> token);
        $sql -> execute();

        return true;
    }
}