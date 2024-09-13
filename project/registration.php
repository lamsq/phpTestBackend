<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Main page</title> <!-- page title-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><!-- Bootstrap CSS -->
</head>

<?php 
    session_start();

    if(isset($_COOKIE['logged'])){ //редирект на страницу изменения данных
        header("location: profile.php");
    }

    require_once ("../db_connect.php");  

    //функция для предотвращения использования спецсимволов в даннях
    function sanitized($input) {  
        $input = strip_tags($input); 
        $input = htmlspecialchars($input); 
        $input = stripslashes($input);  
        $input = trim($input); 
        return $input;
    }

    $error = '';
    $error_reg='';

    // Валидация данных пользователя на стороне сервера 

    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        if (isset($_POST['name']) && preg_match('/[a-zA-Z\d,.!*)#(?_]+/', ($_POST['name']))){
            $name = htmlentities(sanitized($_POST['name']));
        } else if (empty($_POST['name'])) {
            $error_reg.= '<p class="">Введите имя пользователя;</p>';
        } else {
            $error_reg.= '<p class="">Неверный формат имени пользователя;</p>';
        } 

        // validate phone 
        if (isset($_POST['phone']) && preg_match('/^\+?\d{1,14}$/', ($_POST['phone']))){
            $phone = htmlentities(sanitized($_POST['phone']));
        } else if (empty(($_POST['phone']))) {
            $error_reg.= '<p class="">Введите номер телефона;</p>';
        } else {
            $error_reg.= '<p class="">Неверный формат телефона, номер должен содержать только цифры;</p>';
        } 


        // validate email 
        if (isset($_POST['email']) && preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', ($_POST['email']))){
            $email = htmlentities(sanitized($_POST['email']));
        } else if (empty($_POST['email'])) {
            $error_reg.= '<p class="">Введите email;</p>';
        } else {
            $error_reg.= '<p class="">Неверный формат email;</p>';
        } 


        // validate password 
        if (strlen(($_POST['password'])<8 && strlen(($_POST['password'])>0))){
            $error_reg.= '<p class="">Слишком короткий пароль;</p>';
        }
        if ((!empty($_POST['password']) && !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[*.!_\-()a-zA-Z\d]+$/', ($_POST['password']))) || str_contains($_POST['password']," ")){
            $error_reg.= '<p class="">Пароль должен содержать заглавную букву, строчную букву и символ;</p>';
        }
        if (empty(($_POST['password']))) {
            $error_reg.= '<p class="">Введите пароль;</p>';
        } else {                
            $password = htmlentities(sanitized($_POST['password']));
            if($password==htmlentities(sanitized($_POST['confirmPassword'])) && !empty($_POST['confirmPassword'])){
                $password_hash = password_hash($password, PASSWORD_BCRYPT);                    
            } else if (empty($_POST['confirmPassword'])){
                $error_reg.='<p class="">Подтвердите пароль;</p>';
            } else {
                $error_reg.='<p class="">Пароли не совпадают;</p>';
            }
        } 

        if(empty($error_reg)){ //при отсутствии ошибок в данных

            $userFound = false; 
            $query = "SELECT * FROM users"; 
            $result = mysqli_query($db_connection, $query);

            if($result){
                while($row = mysqli_fetch_assoc($result)){                      
                    if(str_contains(implode($row), $phone)){ 
                        $userFound = true;
                        $error_reg.='<p class="">Пользователь с данным номером уже зарегистрирован;</p>';
                        break; 
                    }if(str_contains(implode($row), $name)){ 
                        $userFound = true;
                        $error_reg.='<p class="">Пользователь с данным именем уже зарегистрирован;</p>';
                        break; 
                    }if(str_contains(implode($row), $email)){ 
                        $userFound = true;
                        $error_reg.='<p class="">Пользователь с данным email уже зарегистрирован;</p>';
                        break; 
                    }
                }
                
                if (!$userFound){ 

                    $name = mysqli_real_escape_string($db_connection, $name);
                    $phone = mysqli_real_escape_string($db_connection, $phone);
                    $email = mysqli_real_escape_string($db_connection, $email);
                    $password_hash = mysqli_real_escape_string($db_connection, $password_hash);
                    
                    $addUserStatement = "INSERT INTO users (name, phone, email, password) VALUES ('$name', '$phone', '$email', '$password_hash')";
                    $result = mysqli_query($db_connection, $addUserStatement); //passes the statement

                    if ($result) { 
                        setcookie('reg', "Пользователь зарегистрирован;", time()+10, '/', '', true, true);       
                        require ("logout.php");
                        header("Location: index.php");
                    } else {
                        echo "Ошибка добавления пользователя;</br> ".mysqli_error($db_connection);
                    }
                }                 
            } else { 
                echo "<div class=\"\">";
                echo "<h5>Что-то пошло не так, попробуйте снова;</h5>";
                echo "</div>";
            }
        }                    
    } 
?>

<body>    
    <header class="d-flex justify-content-center align-items-center p-3">
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='index.php';" value='Главная страница'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='registration.php';" value='Регистрация'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='authorization.php';" value='Авторизация'></div>
        <div class="me-5"><input type="button" class="btn btn-primary" onclick="location.href='profile.php';" value='Профиль пользователя'></div>
    </header>
    <main>
        <body>        
            <form class="w-50 mx-auto" method="post" novalidate action="<?php echo $_SERVER['PHP_SELF'];?>">

                <div class="mb-3 form-group-div">
                    <label>Имя:</label>
                    <input type="text" name="name" class="form-control" value="<?php if(isset($_POST['name'])) echo htmlspecialchars($_POST['name']);?>">
                </div>   

                <div class="mb-3 form-group-div">
                    <label>Телефон:</label>
                    <input type="text" name="phone" class="form-control" value="<?php if(isset($_POST['phone'])) echo htmlspecialchars($_POST['phone']);?>">
                </div> 

                <div class="mb-3 form-group-div">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" placeholder="example@email.com" value="<?php  if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']);?>">
                </div>    

                <div class="mb-3 form-group-div">
                    <label>Пароль:</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3 form-group-div">
                    <label>Подтвердите пароль:</label>
                    <input type="password" name="confirmPassword" class="form-control">
                </div>

                <?php if (!empty($error_reg)) {echo '<div class="error">' . $error_reg . '</div>'; } ?>

                <div class="mb-3 form-group-b">
                    <input type="submit" class="btn btn-primary" name="reg" class="reg_button" value="Зарегистрироваться">
                </div>
            </form>
        </body>
    </main>
</body>
</html>
                  
                  


