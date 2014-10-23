<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;
use tdt4237\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::guest()) {
            $this->render('newUserForm.twig', []);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }
    


    function create()
    {
        $request = $this->app->request;
        date_default_timezone_set('UTC');
        $token = "" + date('jnY');
        if($request->post('csrfToken') == $token){
            $username = $request->post('user');
            $username = $this->xecho($username);
            $pass = $request->post('pass');
            //$pass = xecho($pass);
            $securityquestion = $request->post('securityquestion');
            $securityquestion = $this->xecho($securityquestion);
            $securityanwser = $request->post('securityanswer');
            $securityanwser = $this->xecho($securityanwser);

            $hashed = Hash::make($pass);
            $hashedanwser = Hash::make($securityanwser);

            $user = $this->makeUserData($hashed,$hashedanwser,$securityquestion,$username);

            $validationErrors = User::validate($user);
            $validationErrorsExtended = User::validateSecurity($securityquestion, $securityanwser, $validationErrors);
            $validationErrorsExtended2 = User::validatePass($pass, $validationErrorsExtended);

            if (sizeof($validationErrorsExtended2) > 0) {
                $errors = join("<br>\n", $validationErrorsExtended2);
                $this->app->flashNow('error', $errors);
                $this->render('newUserForm.twig', ['username' => $username]);
            } else {
                $user->save();
                $this->app->flash('info', 'Thanks for creating a user. Now log in.');
                $this->app->redirect('/login');
            }
        }
        else {
            //$this->app->flash('info', 'Incorrect csrf.');
            $this->app->redirect('/user/new');
        }
    }
    
    function makeUserData($hashed,$hashedanswer,$securityquestion,$username){
        $user = User::makeEmpty();
        
        $user->setUsername($username);
        $user->setHash($hashed);
        $user->setSecurityQuestion($securityquestion);
        $user->setSecurityAnswer($hashedanswer);
        return $user;
    }
        
    function all()
    {
        $users = User::all();
        $this->render('users.twig', ['users' => $users]);
    }

    function logout()
    {
        Auth::logout();
        $this->app->redirect('/?msg=Successfully logged out.');
    }

    function show($username)
    {
        $username = $this->xecho($username);
        $user = User::findByUser($username);


        $this->render('showuser.twig', [
            'user' => $user,
            'username' => $username
        ]);
    }

    function edit()
    {
        if (Auth::guest()) {
            $this->app->flash('info', 'You must be logged in to edit your profile.');
            $this->app->redirect('/login');
            return;
        }

        $user = Auth::user();

        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        }
        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            date_default_timezone_set('UTC');
            $token = "" + date('jnY');
            if($request->post('csrfToken') == $token){
                $email = $request->post('email');
                $email = $this->xecho($email);

                $bio = $request->post('bio');
                $bio = $this->xecho($bio);

                $age = $request->post('age');
                $age = $this->xecho($age);
                //$imageurl = $this->xecho("web/profilepictures/". $user->getUserName().".".basename( $_FILES["uploadFile"]["name"]));
                $imageurl = $this->xecho($user->getUserName().".".basename( $_FILES["uploadFile"]["name"]));

                $user->setEmail($email);
                $user->setBio($bio);
                $user->setAge($age);
                $user->setImageurl($imageurl);

                if (! User::validateAge($user)) {
                    $this->app->flashNow('error', 'Age must be between 0 and 150.');

                } else {

                    if($this->uploadeprofilepicture($imageurl)) {
                        $user->save();
                        $this->app->flashNow('info', 'Your profile was successfully saved.');
                    }
                }
            }
            else {
                //$this->app->flashNow('info', 'Incorrect csrf.');
            }
        }
        $this->render('edituser.twig', ['user' => $user]);
    }
    function uploadeprofilepicture( $imageurl ){
        $uploadOK = 1;

        if(basename( $_FILES["uploadFile"]["name"]) === "") {
            return true;
        }
        
        $validationErrors = [];
        
        switch ($_FILES['uploadFile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return false;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            array_push($validationErrors, "Image size is too big.");
            break;
        default:
            array_push($validationErrors, 'An error occurred.');
        }
        
        if(sizeof($validationErrors) > 0) {
            $errors = join("<br>\n", $validationErrors);
            $this->app->flashNow('error', $errors);
            return false;
        }
        
        $size = getimagesize($_FILES['uploadFile']['tmp_name']);
        if(!$size) {
            $this->app->flashNow('error', "Image format is not accepted.");
            return false;
        }

        if($_FILES["uploadFile"]["size"]>500000){
            array_push($validationErrors, "Image size is too big.");
            $uploadOK = 0;
        }

        if(!($_FILES["uploadFile"]["type"]==="image/gif" ||
                $_FILES["uploadFile"]["type"]==="image/png" ||
                $_FILES["uploadFile"]["type"]==="image/jpeg")){
            array_push($validationErrors, "Image format is not accepted.");
            $uploadOK = 0;
        }
        if ($uploadOK===1){
            return move_uploaded_file($_FILES['uploadFile']['tmp_name'], "web/profilepictures/". $imageurl);
        }
        else {
            $errors = join("<br>\n", $validationErrors);
            $this->app->flashNow('error', $errors);
        }
    }
    function xssafe($data,$encoding='UTF-8')
    {
       return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401,$encoding);
    }

    function xecho($data)
    {
       return $this->xssafe($data);
    }
   }
