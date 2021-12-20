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
    $firstname = trim($data['first_name']);
    $lastname = trim($data['last_name']);
    $email = trim($data['email']);
    $password = trim($data['password']);

    $hash = md5($password . SALT);

    if(preg_match('/^.{8,}$/', $password)) {
      $link   = connect();
      $query  = 'insert into users(first_name, last_name, email, password) values("'.$firstname.'","'.$lastname.'","'.$email.'","'.$hash.'")';
      $result = mysqli_query($link, $query);
  
      if ($result) {
        echo "Sign up successful";
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
      } else  {
        echo "Sign up failed";
      }
    
      mysqli_close($link);
    } else {
      echo 'password must be 8 characters or longer';
    }
  }
  header('Location: index.php');
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
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $_POST['email'];
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

function getAllProducts()
{
    $link     = connect();
    $query    = 'select * from products order by pinned DESC';
    $products = mysqli_query($link, $query);

    mysqli_close($link);
    return $products;
}

function getViewedProducts()
{
    $link     = connect();
    $query    = 'select * from products order by pinned DESC';
    $products = mysqli_query($link, $query);

    mysqli_close($link);
    return $products;
}

function getProductUser($id)
{
    $link    = connect();
    $query   = 'select * from users where id = "'.$id.'"';
    $success = mysqli_query($link, $query);

    while($row = mysqli_fetch_array($success)) 
    {
        return $row;
    }

    mysqli_close($link);
}

function getProductInfo($id)
{
    $link     = connect();
    $query   = 'select * from products where id = "'.$id.'"';
    $success = mysqli_query($link, $query);

    while($row = mysqli_fetch_array($success)) 
    {
        return $row;
    }

    mysqli_close($link);
}

function addToRecentlyViewed($id)
{
  if(!isset($_COOKIE['rv_'.$id])) {
    setcookie("rv_".$id, $id, time()+3600);
  }
}

function uploadProduct($data, $file, $user_id)
{

    $title = trim($data['title']);
    $price = trim($data['price']);
    $description = trim($data['description']);
    $picture = md5($title.time());

    $moved   = move_uploaded_file($file['picture']['tmp_name'], 'products/'.$picture);

    if($moved)
    {
        $link    = connect();
        $query   = 'insert into products (title, price, description, picture, user, pinned, downvote)
        values ("'.$title.'", "'.$price.'", "'.$description.'", "'.$picture.'", "'.$user_id.'", 0, 0)';
        $result = mysqli_query($link, $query);
        
        if ($result) {
          echo "You have succesfully posted a new product!";
        }

        mysqli_close($link);

        header('Location: index.php');

        return $result;
    }

    return false;
}

function getUser($email) {
    $link     = connect();
    $query   = 'select * from users where email = "'.$email.'"';
    $success = mysqli_query($link, $query);

    while($row = mysqli_fetch_array($success)) 
    {
        return $row;
    }

    mysqli_close($link);
}

function deleteProduct($id)
{
    $link    = connect();
    $query   = 'delete from products where id = "'.$id.'"';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    header('Location: index.php');
    return $success;
}

function togglePin($old_val, $id) {

  $old_val == 0? $new_val = 1 : $new_val = 0;

  $link     = connect();
  $query    = 'update products set pinned = "'. $new_val .'" where id = "'. $id .'"';
  
  $success = mysqli_query($link, $query);
  mysqli_close($link);
  
  header('Location: index.php');

  return $success;
}