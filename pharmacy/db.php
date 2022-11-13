<?php

$dsn = "mysql:host=sql7.freemysqlhosting.net;dbname=sql7568194;charset=utf8mb4" ;
$user = "sql7568194" ;
$pass = "FUQqhN1nBc" ;



try {
    $db = new PDO($dsn, $user, $pass) ;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) ;
} catch (Exception $ex) {
   echo "DB Connection Error : " .  $ex->getMessage() ;
   exit ;
}
