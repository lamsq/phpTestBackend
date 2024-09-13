<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Main page</title> <!-- page title-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><!-- Bootstrap CSS -->
</head>

<?php 
    session_start();
    require_once ("../db_connect.php"); 

    function sanitized($input) {   //функция для избежания использования спецсимволов в данных
        $input = strip_tags($input); 
        $input = htmlspecialchars($input); 
        $input = stripslashes($input);  
        $input = trim($input); 
        return $input;
    }

    $error = '';
    $error_upd='';

    //валидация данных пользователя
    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        //валидация имени
        if (isset($_POST['name']) && preg_match('/[a-zA-Z\d,.!*)#(?_]+/', ($_POST['name']))){
            $name = htmlentities(sanitized($_POST['name']));
        } else if (empty($_POST['name'])) {
            $error_upd.= '<p class="">Введите имя пользователя;</p>';
        } else {
            $error_upd.= '<p class="">Неверный формат имени пользователя;</p>';
        } 

        //валидация телефона
        if (isset($_POST['phone']) && preg_match('/^\+?\d{1,14}$/', ($_POST['phone']))){
            $phone = htmlentities(sanitized($_POST['phone']));
        } else if (empty(($_POST['phone']))) {
            $error_upd.= '<p class="">Введите номер телефона;</p>';
        } else {
            $error_upd.= '<p class="">Неверный формат телефона, номер должен содержать только цифры;</p>';
        } 
 
        //валидация почты
        if (isset($_POST['email']) && preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', ($_POST['email']))){
            $email = htmlentities(sanitized($_POST['email']));
        } else if (empty($_POST['email'])) {
            $error_upd.= '<p class="">Введите email;</p>';
        } else {
            $error_upd.= '<p class="">Неверный формат email;</p>';
        } 

        //валидация старого пароля
        if (isset($_POST['oldPassword']) && strlen($_POST['oldPassword'])<8){
            $error_upd.= '<p class="">Некоррекный формат пароля;</p>';
        } else if (empty(sanitized($_POST['oldPassword']))) {
            $error_upd.= '<p class="">Введите пароль;</p>';
        } else {
            $oldPassword = htmlentities(sanitized($_POST['oldPassword']));
        }

        //валидация нового пароля
        if (strlen(($_POST['newPassword'])<8 && strlen(($_POST['newPassword'])>0))){
            $error_upd.= '<p class="">Новый пароль слишком короткий;</p>';
        }
        if ((!empty($_POST['newPassword']) && !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[*.!_\-()a-zA-Z\d]+$/', ($_POST['newPassword']))) || str_contains($_POST['newPassword']," ")){
            $error_upd.= '<p class="">Новый пароль должен содержать заглавную букву, строчную букву и символ;</p>';
        }
        if (empty(($_POST['newPassword']))) {
            $error_upd.= '<p class="">Введите новый пароль;</p>';
        } else {                
            $newPassword = htmlentities(sanitized($_POST['newPassword']));
            if($newPassword==htmlentities(sanitized($_POST['confirmNewPassword'])) && !empty($_POST['confirmNewPassword'])){
                $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);                    
            } else if (empty($_POST['confirmNewPassword'])){
                $error_upd.='<p class="">Подтвердите новый пароль;</p>';
            } else {
                $error_upd.='<p class="">Пароли не совпадают;</p>';
            }
        } 

        if(empty($error_upd)){  //изменение данных при отсутствии ошибок          

            $userId = $_SESSION["user"]["id"];
            $query = "SELECT * FROM users WHERE id != $userId"; 
            $result = mysqli_query($db_connection, $query);
            $userFound = false;

            if($result){ 
                while($row = mysqli_fetch_assoc($result)){  //проверка свободных данных                     
                    if(str_contains(implode($row), $phone)){ 
                        $userFound = true;
                        $error.='<p class="">Пользователь с данным номером уже зарегистрирован;</p>';
                    }if(str_contains(implode($row), $name)){ 
                        $userFound = true;
                        $error.='<p class="">Пользователь с данным именем уже зарегистрирован;</p>';
                    }if(str_contains(implode($row), $email)){ 
                        $userFound = true;
                        $error.='<p class="">Пользователь с данным email уже зарегистрирован;</p>';
                    }if($userFound){
                        break;
                    }
                }
                
                if (!$userFound){ //запрос в бд

                    $stmt = $db_connection->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("s", $_SESSION['user']['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if (password_verify($oldPassword, $row['password'])) {//при совпадении паролей

                        $name = mysqli_real_escape_string($db_connection, $name);
                        $phone = mysqli_real_escape_string($db_connection, $phone);
                        $email = mysqli_real_escape_string($db_connection, $email);
                        $password_hash = mysqli_real_escape_string($db_connection, $password_hash);
                        
                        $addUserStatement = "UPDATE users SET name = '$name', phone = '$phone', email = '$email', password = '$password_hash' WHERE id = $userId;";
                        $result = mysqli_query($db_connection, $addUserStatement); //passes the statement

                        if ($result) {  //при успешном изменении данных 
                            setcookie('reg', "Данные успешно изменены, необходима повторная авторизация;", time()+10, '/', '', true, true);                          
                            header("Location: logout.php");
                        } else {
                            echo "Ошибка изменения данных пользователя;</br> ".mysqli_error($db_connection);
                        } 
                    } else {
                        $error.= '<p class="">Данные для авторизации недействительны;</p>';
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

        <?php  
        if(!isset($_COOKIE['logged'])){
            header("location: index.php");
        }        
        ?>

        <body class="w-50 mx-auto">
            <div><h2>Здесь вы можете изменить личные данные<h2></div>

            <form method="POST" novalidate action="<?php echo $_SERVER['PHP_SELF'];?>">

                <div class="mb-3 form-group-div">
                    <label>Имя:</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $_SESSION['user']['name'];?>">
                </div>   

                <div class="mb-3 form-group-div">
                    <label>Телефон:</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $_SESSION['user']['phone'];?>">
                </div> 

                <div class="mb-3 form-group-div">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" placeholder="example@email.com" value="<?php echo $_SESSION['user']['email'];?>">
                </div>    

                <div class="mb-3 form-group-div">
                    <label>Старый пароль:</label>
                    <input type="password" name="oldPassword" class="form-control">
                </div>

                <div class="mb-3 form-group-div">
                    <label>Новый пароль:</label>
                    <input type="password" name="newPassword" class="form-control">
                </div>

                <div class="mb-3 form-group-div">
                    <label>Подтвердите новый пароль:</label>
                    <input type="password" name="confirmNewPassword" class="form-control">
                </div>

                <?php if (!empty($error_upd)) {echo '<div class="error">' . $error_upd . '</div>'; } ?>
                <?php if (!empty($error)) {echo '<div class="error">' . $error . '</div>'; } ?>

                <div class="mb-3 form-group-b">
                    <input type="submit" class="btn btn-primary" name="reg" class="reg_button" value="Обновить данные">
                </div>
            </form>

            <div class="me-5">
                <input type="button" class="btn btn-primary" onclick="location.href='logout.php';" value='Выйти'>
            </div>
           
        </body>        
    </main>
</body>
</html>
                  
                  


