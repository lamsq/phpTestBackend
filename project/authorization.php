<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Main page</title> <!-- page title-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><!-- Bootstrap CSS -->
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>

<?php
    session_start();

    if(isset($_COOKIE['logged'])){ //редирект залогиненных пользователей на страницу профиля
        header("location: profile.php");
    }

    require ("../db_connect.php");
    define('SMARTCAPTCHA_SERVER_KEY', 'ysc2_U18iakS9ehDWkW3tmpBC32vRblseSvLHap2UDiNtb504a8aa'); //ключ капчи

    $error= '';

    function sanitized($input) {  //функция для предотвращения использования спецсимволов
        $input = strip_tags($input); 
        $input = htmlspecialchars($input); 
        $input = stripslashes($input);  
        $input = trim($input); 
        return $input;
    }

    //валидация данных
    if ($_SERVER["REQUEST_METHOD"]=="GET" && isset($_GET['loginBtn'])) {

        $captchaChecked = false;

        $token = $_GET['smart-token'];
        if (check_captcha($token)) {
            $captchaChecked = true;
        } else {
            $error.= '<p class="">Пройдите капчу;</p>';
        }

        if (!isset($_GET['login']) || empty(sanitized($_GET['login']))){
            $error.= '<p class="">Введите номер телефона или email;</p>';
        } else {
            $login = htmlentities(sanitized($_GET['login']));
        }

        // validate password
        if (isset($_GET['password']) && strlen($_GET['password'])<8){
            $error.= '<p class="">Некоррекный формат пароля;</p>';
        } else if (empty(sanitized($_GET['password']))) {
            $error.= '<p class="">Введите пароль;</p>';
        } else {
            $password = htmlentities(sanitized($_GET['password']));
        }

        if (empty($error)) { //Работа с БД при отсутствии ошибок

            //проверка данных записей из бд для нахождения соответствия с введенными данными
            $stmt = $db_connection->prepare("SELECT * FROM users WHERE email = '$login'");            
            $stmt->execute();
            $result = $stmt->get_result();            
            $existedUser = false;
            $row;

            if ($result->num_rows > 0) {
                $existedUser = true;                
            } else {
                $stmt = $db_connection->prepare("SELECT * FROM users WHERE phone = '$login'");            
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $existedUser = true;                    
                } else {
                    $error.= '<p class="">Пользователь незарегистрирован;</p>';
                }
            }

            if($existedUser){ //если пользователь найден, происходит установки куки и добавление массива в глобальный массив сессии
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION["user"] = $row;
                    setcookie('reg', "Успешная авторизация;", time()+10, '/', '', true, true);
                    setcookie('logged', true, time()+30*24*60*60, '/', '', true, true);
                    header("location: index.php");                    
                } else {
                    $error.= '<p class="">Данные для авторизации недействительны;</p>';
                }
            }
        }        
    }
    
    //Яндекс капча
    function check_captcha($token) {
        $ch = curl_init();
        $args = http_build_query([
            "secret" => SMARTCAPTCHA_SERVER_KEY,
            "token" => $token,
            "ip" => $_SERVER['REMOTE_ADDR'], 
        ]);
        curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
            return true;
        }
        $resp = json_decode($server_output);
        return $resp->status === "ok";
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
        <form class="w-50 mx-auto" method="GET" novalidate action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="mb-3 form-group-div">
                <label>Номер телефона или Email:</label>
                <input type="text" id="login" name="login" class="form-control" value="<?php if(isset($_POST['login'])) echo htmlspecialchars($_POST['login']);?>">
            </div> 
                            
            <div class="mb-3 form-group-div">
                <label>Пароль:</label>
                <input type="password" id="password_input" name="password" class="form-control" >
            </div>
            <div style="height: 100px" id="captcha-container" class="smart-captcha mb-3 form-group-div" data-sitekey="ysc1_U18iakS9ehDWkW3tmpBCcRde2mP6Wj8t3l3M4Oe707db468c"></div>                
                        
            <?php if (!empty($error)) { echo '<div class="error">' . $error . '</div>'; }?>
                                                        
            <div class="mb-3 form-group-b">
                <input type="submit" class="btn btn-primary" name="loginBtn" class="loginBtn" value="Авторизоваться">
            </div>    
           
        </form>
    </main>
</body>
</html>
                  
                  


