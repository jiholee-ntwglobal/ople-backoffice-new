<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:05
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Technical Support Team">

    <title>Openmarket Manage System</title>

    <!-- Bootstrap Core CSS -->
    <link href="/qten/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="/qten/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/qten/dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="/qten/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php
    if(isset($add_stylesheet)){
        foreach ($add_stylesheet as $stylesheet){
            echo '<link href="' . $stylesheet .'" rel="stylesheet" type="text/css">';
        }
    }
    ?>
    <style>
        #page-wrapper { margin:0; }
    </style>

    <!-- jQuery -->
    <script src="/qten/js/jquery-1.11.2.min.js"></script>
    <script src="/qten/js/jquery-ui.min.js"></script>


    <!-- Bootstrap Core JavaScript -->
    <script src="/qten/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="/qten/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="/qten/dist/js/sb-admin-2.js"></script>

    <?php
    if(isset($add_script)){
        foreach ($add_script as $script){
            //echo '<script src="' . $script .'"></script>';
        }
    }?>

</head>

<body>

<?php if(isset($left_menu)) echo $left_menu; ?>