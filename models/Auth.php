<?php //ARQUIVO 2
require_once 'dao/UserDaoMysql.php';
class Auth {
    //vamos criar um construtor para salvar os dados mandados para a autenticação
    private $pdo;
    private $base;
    private $dao;//orientaçaõ a objeto
    
    public function __construct(PDO $pdo, $base){
        $this->pdo = $pdo;
        $this -> base = $base;
        //salvando os dados do usuário no banco de dados dentro dessa função
        $this -> dao = new UserDaoMysql($this->pdo);//retirei dos códigos abaixo porque se repetia
    }

    public function checkToken() {
        if(!empty($_SESSION['token'])) {
            $token = $_SESSION['token']; //armazenar esse token
            
            //verificando se tem o token
            $user = $this -> dao->findByToken($token);
            //verificando se achou o token ou o usuário
            if($user) {
                return $user;
            }
        }
        header("Location: ".$this->base."/login.php");
        exit;
    }

    public function validateLogin($email, $password) {
        
        //função que vai verificar o email do usuário no banco de dados e salvar na variavel
        $user = $this -> dao -> findByEmail($email);
        //verificar se achou o usuário
        if($user) {
            //verificação da senha e do email direto do banco de dados
            if(password_verify($password, $user->password)) {
                //gerando o token com numeros aleatórios e a data atual
                $token = md5(time().rand(0, 9999));
                //salvando o token na cessão
                $_SESSION['token'] = $token;
                //joguei o token dentro das informações do usuário
                $user -> token = $token;
                //salvando a senha também no banco de dados
                $this -> dao -> update($user);

                return true;
            }
        }
        return false;
    }

    //função que vai verificar se um email ja existe
    public function emailExists($email) {
        
        //verificando se ja existe o email no banco de dados
        /*if($userDao -> findByEmail($email)) {
            return true;
        }else {
            return false;
        } ou pode fazer assim:*/
        return $this -> dao -> findByEmail($email) ? true : false;
    }

    //criando a que vai fazer os registro da função
    public function registerUser($name, $email, $password, $birthdate) {

        //gerando o hash para a senha do usuário
        $hash = password_hash($password, PASSWORD_DEFAULT);

        //criando um token para o usuário com numeros aleatórios e a data atual
        $token = md5(time().rand(0, 9999));

        //usando a função para criar o usuário antes de reunir todas as informações
        $newUser = new User();
        //preenchendo as informações do usuário
        $newUser -> name = $name;
        $newUser -> email = $email;
        $newUser -> password = $hash;
        $newUser -> birthdate = $birthdate;
        $newUser -> token = $token;

        //inserindo os dados com a variável que foi criada
        $this -> dao -> insert($newUser);

        //inserindo o token na seção
        $_SESSION['token'] = $token;
    }
}