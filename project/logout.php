<?php
//Скрипт для удаления куки и завершения сессии
session_start();
setcookie('logged', 'false', time()-10000, '/'); 
if (session_destroy()) {    
    session_unset();    
    header("Location: index.php");
    exit;
}
?>
