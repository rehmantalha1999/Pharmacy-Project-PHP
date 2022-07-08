<?php
require "./pharmacy/db.php";
function registerUser($email, $name, $password, $city, $district, $address)
{
    global $db;
    $stmt = $db->prepare("INSERT INTO family(email, name, password, city, district, address) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$email, $name, $password, $city, $district, $address]);
}

function checkUser($email, $pass)
{
    global $db;

    $stmt = $db->prepare("select * from family where email=?");
    $stmt->execute([$email]);
    if ($stmt->rowCount()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return password_verify($pass, $user["password"]);
    }
    return false;
}

function validSession()
{
    return isset($_SESSION["user"]);
}

function getUser($email)
{
    global $db;
    $stmt = $db->prepare("select * from family where email=?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
