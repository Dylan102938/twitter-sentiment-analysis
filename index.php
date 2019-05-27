<?php

    session_start();
    if (isset($_POST['submit'])) {

        $query = $_POST['twittersearch'];
        $_SESSION['query'] = $query;
        header("Location: result.php");

    }

?>

<html>

    <head>

        <title>Twitter Sentiment Analysis</title>

        <link href = "./css/index.css" rel = "stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Catamaran:400,600,700" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css"
              integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay"
              crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Dosis:800&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="logo.png">

    </head>

    <body>

        <div id = "sentiment">

            <div class = "header">

                <i class="fab fa-twitter-square"></i>
                <span class = "title">mytwittersent</span>
                <br>
                <span class = "subtitle">How is Twitter feeling today?</span>

            </div>

            <form method = "post">

                <input type = "text" id = "twittersearch" name = "twittersearch" placeholder = "search..." autocomplete = "off">
                <br>
                <br>
                <input type = "submit" id = "submit" class = "sexy-button" name = "submit" style = "float: right; font-size: 20px;">

            </form>

        </div>

    </body>

</html>
