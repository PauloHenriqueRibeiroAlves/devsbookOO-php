<?php //ARQUIVO 2
class User {
    public $id;
    public $email;
    public $password;
    public $name;
    public $birthdate;
    public $city;
    public $work;
    public $avatar;
    public $cover;
    public $token;
//propriedade que estava dando erro no calculo de idade
    public $ageYears;
    public $followers;
    public $following;
    public $photos;
}
//interface para a criação do DAO
interface UserDAO {
    //achar o usuáriuo pelo token
    public function findByToken($token);

    public function findByEmail($email);

    public function findById($id);

    public function update(User $u);

    public function insert(User $u);
}