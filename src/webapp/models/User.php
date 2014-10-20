<?php

namespace tdt4237\webapp\models;

use tdt4237\webapp\Hash;

use tdt4237\webapp\Auth;

class User
{
    const INSERT_QUERY = "INSERT INTO users(user, pass, email, age, bio, isadmin, securityquestion, securityanwser, imageurl) VALUES(?,?,?,?,?,?,?,?,?)";
    const UPDATE_QUERY = "UPDATE users SET email=?, age=?, bio=?, isadmin=? WHERE id=?";
    const FIND_BY_NAME = "SELECT * FROM users WHERE user=?";
    const RESET_PASSWORD = "UPDATE users SET pass = ? WHERE id=?";
    
    const MIN_USER_LENGTH = 3;
    const MIN_PASS_LENGTH = 8;
    const MAX_USER_LENGTH = 20;

    protected $id = null;
    protected $user;
    protected $pass;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $age;
    protected $isAdmin = 0;
    protected $securityquestion;
    protected $securityanswer;
    protected $imageurl;

    static $app;

    function __construct()
    {
    }

    static function make($id, $username, $hash, $email, $bio, $age, $isAdmin, $securityquestion, $securityanwser, $imageurl)
    {
        $user = new User();
        $user->id = $id;
        $user->user = $username;
        $user->pass = $hash;
        $user->email = $email;
        $user->bio = $bio;
        $user->age = $age;
        $user->isAdmin = $isAdmin;
        $user->securityquestion = $securityquestion;
        $user->securityanswer = $securityanwser;
        $user->imageurl = $imageurl;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        if ($this->id === null) {
            $stmt = self::$app->db->prepare(self::INSERT_QUERY);
            $stmt->execute(array($this->user,  $this->pass,  $this->email,  $this->age,  $this->bio,  $this->isAdmin, $this->securityquestion,  $this->securityanswer, $this->imageurl));
        } else {
            
            $stmt = self::$app->db->prepare(self::UPDATE_QUERY);
            $stmt->execute(array($this->email,  $this->age,  $this->bio,  $this->isAdmin,  $this->id));
        }
        return $stmt;
    }
    
    function resetPassword(){
        $stmt = self::$app->db->prepare(self::RESET_PASSWORD);
        $stmt->execute(array($this->pass,  $this->id));
    }

    function getId()
    {
        return $this->id;
    }

    function getUserName()
    {
        return $this->user;
    }

    function getPasswordHash()
    {
        return $this->pass;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function getAge()
    {
        return $this->age;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }
    
    function getSecurityQuestion(){
        return $this->securityquestion;
    }
    
    function getSecurityAnswer(){
        return $this->securityanswer;
    }

    function getImageurl(){
        return $this -> imageurl;
    }
    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->user = $username;
    }

    function setHash($hash)
    {
        $this->pass = $hash;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }


    function setAge($age)
    {
        $this->age = $age;
    }
    
    function setSecurityQuestion($question){
        $this->securityquestion = $question;
    }
    
    function setSecurityAnswer($anwser){
        $this->securityanswer = $anwser;
    }
    function setImageurl($imageurl){
        $this -> imageurl = $imageurl;
    }
    /**
     * The caller of this function can check the length of the returned 
     * array. If array length is 0, then all checks passed.
     *
     * @param User $user
     * @return array An array of strings of validation errors
     */
    static function validate(User $user)
    {
        $validationErrors = [];

        if (strlen($user->user) < self::MIN_USER_LENGTH) {
            array_push($validationErrors, "Username too short. Min length is " . self::MIN_USER_LENGTH);
        }
        if (self::findByUser($user->user)!=null){
             array_push($validationErrors, "Username ".$user->user ." already exist");
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $user->user) === 0) {
            array_push($validationErrors, 'Username can only contain letters and numbers');
        }
        if (strlen($user->user) > self::MAX_USER_LENGTH) {
            array_push($validationErrors, "Username too long. Maximum length is " . self::MAX_USER_LENGTH);
        }

        return $validationErrors;
    }
    
    static function validatePass($pass, $validationErrors)
    {
        if(strlen($pass) < self::MIN_PASS_LENGTH) {
            array_push($validationErrors, "Password too short. Min length is " . self::MIN_PASS_LENGTH);
        }
        
        return $validationErrors;
    }
    
    static function validateSecurity($question, $answer, $validationErrors){
        if(strlen($question) < 1){
            array_push($validationErrors, "you need to add a security question");
        }
        if(strlen($answer) < 1){
            array_push($validationErrors, "you need to add a security answer");
        }
        
        return $validationErrors;
    }

    static function validateAge(User $user)
    {
        $age = $user->getAge();

        if ($age >= 0 && $age <= 150) {
            return true;
        }

        return false;
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $stmt = self::$app->db->prepare(self::FIND_BY_NAME);
        $stmt->execute(array($username));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    static function deleteByUsername($username)
    {
        $username = htmlspecialchars($username, ENT_QUOTES);
        if (Auth::isAdmin()){
            $query = "DELETE FROM users WHERE user=? ";
            $stmt = self::$app->db->prepare($query);
            $stmt->execute(array($username));
            return $stmt->fetch();
        }
    }

    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['user'],
            $row['pass'],
            $row['email'],
            $row['bio'],
            $row['age'],
            $row['isadmin'],
            $row['securityquestion'],
            $row['securityanwser'],
            $row['imageurl']
        );
    }
}
User::$app = \Slim\Slim::getInstance();
