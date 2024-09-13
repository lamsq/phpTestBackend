<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Main page</title> <!-- page title-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><!-- Bootstrap CSS -->
</head>
<body class="w-50 mx-auto">    
    <header class="d-flex justify-content-center align-items-center p-3">
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='index.php';" value='Главная страница'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='registration.php';" value='Регистрация'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='authorization.php';" value='Авторизация'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='profile.php';" value='Профиль пользователя'></div>
    </header>
    <main>
        <body class="mx-auto d-flex justify-content-center align-items-center p-1">
            <?php 
            if(isset($_COOKIE['reg'])){
                echo "<div class='d-flex justify-content-center'><p>".$_COOKIE['reg']."</p></div>";
            }
            ?>
            <div class="d-flex justify-content-center">
                <h1>Вы находитесь на главной странице!</h1>
            </div>
        </body>        
    </main>
</body>
</html>
                  
                  


