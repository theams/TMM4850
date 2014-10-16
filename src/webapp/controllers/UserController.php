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
            $email = $request->post('email');
            $email = $this->xecho($email);

            $bio = $request->post('bio');
            $bio = $this->xecho($bio);

            $age = $request->post('age');
            $age = $this->xecho($age);
            $imageurl = $this->xecho("web/profilepictures/". $user->getUserName().".".basename( $_FILES["uploadFile"]["name"]));

            $user->setEmail($email);
            $user->setBio($bio);
            $user->setAge($age);
            $user->setImageurl($imageurl);

            if (! User::validateAge($user)) {
                $this->app->flashNow('error', 'Age must be between 0 and 150.');

            } else {

                $this->uploadeprofilepicture( $imageurl);
                $user->save();
                $this->app->flashNow('info', 'Your profile was successfully saved.');
            }
        }
        $this->render('edituser.twig', ['user' => $user]);
    }
    function uploadeprofilepicture( $imageurl ){
        $uploadOK = 1;

        //fungerer ikke, dersom bildet er for stort så krasjer hele siden, men vet ikke hvordan jeg fikser det
      //  if($_FILES["uploadFile"]["size"]>500000){
      //      $this->app->flashNow('info', "Image size is too big.");
       //     $uploadOK = 0;
       // }
        //fungerer ikke og veit ikke hvorfor
        //if($_FILES["uploadFile"]["type"]==="image/gif"){
        //    $this->app->flashNow('info', "Image format is not accepted.");
         //   $uploadOK = 0;
        //}
        if ($uploadOK===1){
        move_uploaded_file($_FILES['uploadFile']['tmp_name'], $imageurl);
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
