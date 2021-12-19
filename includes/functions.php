<?php

define('SALT', 'abc_123!!!');
define('FILE_SIZE_LIMIT', 4000000);

define('DB_HOST',     '127.0.0.1');
define('DB_PORT',     '8889');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'final_project');

function connect()
{
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    if (!$link)
    {
        echo mysqli_connect_error();
        exit;
    }

    return $link;
}

function saveProfile($data)
{
  if (isset($data)) {
    $firstname = $data['first_name'];
    $lastname = $data['last_name'];
    $email = $data['email'];
    $password = $data['password'];

    $hash = md5($password . SALT);

    $link   = connect();
    $query  = 'insert into users(first_name, last_name, email, password) values("'.$lastname.'","'.$firstname.'","'.$email.'","'.$hash.'")';
    $result = mysqli_query($link, $query);

    if ($result) {
      echo "Sign up successful";
      $_SESSION['logged_in'] = true;
      $_SESSION['user_email'] = $email;
    } else  {
      echo "Sign up failed";
    }
  
    mysqli_close($link);

  }
}

function checkSignUp($data)
{
    $valid = true;

    // if any of the fields are missing
    if( trim($data['first_name'])        == '' ||
        trim($data['last_name'])        == '' ||
        trim($data['email'])        == '' ||
        trim($data['password'])        == '' ||
        trim($data['verify_password']) == '')
    {
        $valid = false;
    }
    elseif($data['password'] != $data['verify_password'])
    {
        $valid = false;
    }

    if ($valid) {
      saveProfile($data);
    }
}

function findUser($email, $password)
{
  
    $found = false;

    $link = connect();
    $hash = md5($password . SALT);

    $query   = 'select * from users where email = "'.$email.'" and password = "'.$hash.'"';
    $results = mysqli_query($link, $query);

    if (mysqli_fetch_array($results))
    {
        $found = true;
    }

    mysqli_close($link);
    return $found;
}

function logout() {
  $_SESSION = [];
  setcookie ('PHPSESSID', session_id(), time() - 3600, '/');

  session_destroy();
  session_write_close();
  header('Location: index.php');
  exit();
}

