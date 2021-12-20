<?php
require 'includes/functions.php';
session_start();

if ($_SESSION['logged_in'] && $_SESSION['user_email']) {
    echo '<h2>Welcome: ' . $_SESSION['user_email'] . '</h2>';
}

if ($_GET['sign_up']) {
    checkSignUp($_POST);
}

if ($_GET['login']) {
    if (findUser($_POST['email'], $_POST['password'])) {
        echo "login successful"; 
    } else {
        echo "login failed"; 
    }
}

if ($_GET['logout']) {
    logout();
}

if ($_GET['product_upload']) {
    $user = getUser($_SESSION['user_email']);
    uploadProduct($_POST, $_FILES, $user['id']);
}

if ($_GET['delete_id']) {
    deleteProduct($_GET['delete_id']);
}

if ($_GET['toggle_pin']) {
    echo "HERE";
    $product = getProductInfo($_GET['toggle_pin']);
    togglePin($product['pinned'], $product['id']);
}


$products = getAllProducts();
$viewed_products = getViewedProducts();


?>
<!DOCTYPE html>
<html>
<head>
    <title>COMP 3015</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <div class="container">

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1 class="login-panel text-center text-muted">
                    COMP 3015 Final Project
                </h1>
                <hr/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <?php if ($_SESSION['logged_in'] && $_SESSION['user_email']) {
                    echo '<button class="btn btn-default" data-toggle="modal" data-target="#newItem"><i class="fa fa-photo"></i> New Item</button>';
                    echo '<a href="index.php?logout=true" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>';
                } else {
                    echo '<a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#login"><i class="fa fa-sign-in"> </i> Login</a>';
                    echo '<a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#signup"><i class="fa fa-user"> </i> Sign Up</a>';
                } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <h2 class="login-panel text-muted">
                    Recently Viewed
                </h2>
                <hr/>
            </div>
        </div>
        <?php 
            $count = 0;
            foreach($viewed_products as $product)
            {
                if ($product['id'] == $_COOKIE['rv_'.$product['id']] && $count <= 4) {

                    $productUser = getProductUser($product['user']);

                    if ($productUser['email'] == $_SESSION['user_email']) {
                        $delete_button = '                            
                        <span class="pull-right">
                            <a class="" href="index.php?delete_id='.$product['id'].'" data-toggle="tooltip" title="Delete item">
                                <i class="fa fa-trash"></i>
                            </a>
                        </span>';
                    } else {
                        $delete_button = '<span></span>';
                    }

                    if ($_SESSION['logged_in']) {
                        $downvote_button = '
                        <a class="pull-left" href="" data-toggle="tooltip" title="Downvote item">
                            <i class="fa fa-thumbs-down"></i>
                        </a>';
                    } else {
                        $downvote_button = '<span></span>';
                    }
                    echo '
                    <div class="row">
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <span>
                                    ' . $product['title'] . '
                                </span>
                                ' . $delete_button . '
                            </div>
                            <div class="panel-body text-center">
                                <p>
                                    <a href="product.php?id='.$product['id'].'">
                                        <img class="img-rounded img-thumbnail" src="products/' . $product['picture'] .'"/>
                                    </a>
                                </p>
                                <p class="text-muted text-justify">
                                    '.$product['description'].'
                                </p>
                                ' . $downvote_button . '
                            </div>
                            <div class="panel-footer ">
    
                                <span><a href="mailto:'.$productUser['email'].'" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i> ' . $productUser['first_name'].' '.$productUser['last_name'].'</a></span>
                                <span class="pull-right">$' . number_format($product['price'], 2, '.', '') .'</span>
                            </div>
                        </div>
                    </div>
                    </div>
                    ';

                    $count++;
                }
            }
        ?> 

        <div class="row">
            <div class="col-md-3">
                <h2 class="login-panel text-muted">
                    Items For Sale
                </h2>
                <hr/>
            </div>
        </div>

        <!-- Search -->
        <div class="row">
            <div class="col-md-4">
                    <form class="form-inline">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <input type="text" class="form-control" placeholder="Search"/>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-default" value="Search"/>
                        <button class="btn btn-default" data-toggle="tooltip" title="Shareable Link!"><i class="fa fa-share"></i></button>
                    </form>
                <br/>
            </div>
        </div>

        <!-- Products for sale -->

        <?php 
            foreach($products as $product)
            {
                $productUser = getProductUser($product['user']);

                $product['pinned'] ? $pinned_class = 'panel-warning' : $pinned_class = 'panel-info';
                $product['pinned'] ? $pinned_icon = '<i class="fa fa-dot-circle-o"></i>' : $pinned_icon = '<i class="fa fa-thumb-tack"></i>';

                if ($productUser['email'] == $_SESSION['user_email']) {
                    $delete_button = '                            
                    <span class="pull-right">
                        <a class="" href="index.php?delete_id='.$product['id'].'" data-toggle="tooltip" title="Delete item">                            
                            <i class="fa fa-trash"></i>
                        </a>
                    </span>';
                } else {
                    $delete_button = '<span></span>';
                }

                if ($_SESSION['logged_in']) {
                    $pin_button = '
                    <a class="" href="index.php?toggle_pin='.$product['id'].'" data-toggle="tooltip" title="Unpin item">
                        ' . $pinned_icon . '
                    </a>
                    ';

                    $downvote_button = '
                    <a class="pull-left" href="" data-toggle="tooltip" title="Downvote item">
                        <i class="fa fa-thumbs-down"></i>
                    </a>';
                } else {
                    $pin_button = '<span></span>';
                    $downvote_button = '<span></span>';
                }

                echo '
                <div class="row">
                <div class="col-md-3">
                    <div class="panel '.$pinned_class.'">
                        <div class="panel-heading">
                            ' . $pin_button . '
                            <span>
                                ' . $product['title'] . '
                            </span>
                            '. $delete_button .'
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <a href="product.php?id='.$product['id'].'">
                                    <img class="img-rounded img-thumbnail" src="products/' . $product['picture'] .'"/>
                                </a>
                            </p>
                            <p class="text-muted text-justify">
                                '.$product['description'].'
                            </p>
                            '. $downvote_button .'
                        </div>
                        <div class="panel-footer ">

                            <span><a href="mailto:'.$productUser['email'].'" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i> ' . $productUser['first_name'].' '.$productUser['last_name'].'</a></span>
                            <span class="pull-right">$' . number_format($product['price'], 2, '.', '') .'</span>
                        </div>
                    </div>
                </div>
                </div>
                ';

            }
        ?> 
    </div>

</div>



<div id="login" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="index.php?login=true">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Login</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Login!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="newItem" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="index.php?product_upload=true" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">New Item</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input class="form-control" type="text" name="title" >
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input class="form-control" type="number" name="price" >
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input class="form-control" type="text" name="description" >
                </div>
                <div class="form-group">
                    <label>Picture</label>
                    <input class="form-control" type="file" name="picture" >
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Post Item!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="signup" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="index.php?sign_up=true">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Sign Up</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>First Name</label>
                    <input class="form-control" type="text" name="first_name">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input class="form-control" type="text" name="last_name">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Verify Password</label>
                    <input class="form-control" type="password" name="verify_password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Sign Up!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
</html>
