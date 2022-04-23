<!DOCTYPE html>
<?php
if (!($no_session??false))
    session_start();
?>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title> <?php echo $title??"NyanScan"; ?> </title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="../res/logo-ns-icon.ico">


        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

        <link rel="stylesheet" type="text/css" href="/css/nyanscan.css">
    </head>
    <body class="min-vh-100 ns-theme-bg ns-theme-text">

