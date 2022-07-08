<?php
require "db.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../../project");
}

$user = $_SESSION["user"];

$user = $_SESSION['user']['id'];

function getMedicine($id)
{
    global $db;
    try {
        $stmt = $db->prepare("SELECT drug FROM pharmacy WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        echo "<h1>Error getting Medicine Name</h1>";
    }
}
function getMedicineImage($id)
{
    global $db;
    try {
        $stmt = $db->prepare("SELECT filename FROM pharmacy WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        echo "<h1>Error getting Medicine Name</h1>";
    }
}

// DELETE a medicine
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $drugName = getMedicine($id);
    $drugImage = getMedicineImage($id);
    try {
        unlink("medicines/".$drugImage['filename']);
        $stmt = $db->prepare("DELETE FROM pharmacy where id = ?");
        $stmt->execute([$id]);
        $msg = "{$drugName["drug"]} deleted";
        //    header("location: pharmacy.php");
        
        $page = $_SERVER['PHP_SELF'];
        $sec = "2";
        header("Refresh: $sec; url=$page");
    } catch (PDOException $ex) {
        echo "<h1>Error Deleting Medicine</h1>";
    }
}

$error = array();
$drugSan = '';
$brandSan = '';
$diseaseSan = '';
$patientSan = '';
$stockSan = '';
$filename = '';
$expiry = '';

//Adding a Medicine
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['filename']) && isset($_POST['submit'])) {
    extract($_POST);
    $error = array();
    array_push($error, strlen(trim($drug)) === 0 ? "drug" : "");
    array_push($error, strlen(trim($brand)) === 0 ? "brand" : "");
    array_push($error, strlen(trim($disease)) === 0 ? "disease" : "");
    array_push($error, strlen(trim($patient)) === 0 ? "patient" : "");
    array_push($error, strlen(trim($stock)) === 0 ? "stock" : "");
    array_push($error, strlen(trim($expiry)) === 0 ? "expiry" : "");
    array_push($error, filter_var($stock, FILTER_VALIDATE_INT) ? "" : "stock");
    $drugSan = filter_var($drug, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $brandSan = filter_var($brand, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $diseaseSan = filter_var($disease, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $patientSan = filter_var($patient, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stockSan = filter_var($stock, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $img_name = $_FILES['filename']['name'];
    array_push($error, strlen(trim($_FILES['filename']['name'])) === 0 ? "filename" : "");
    $img_size = $_FILES['filename']['size'];
    $tmp_name = $_FILES['filename']['tmp_name'];
    $err = $_FILES['filename']['error'];
    if ($err === 0) {
        if ($img_size > 10000000) {
            $em = "Sorry, your file is larger than 10Mb.";
            echo "<h1>" . $em . "</h1>";
        } else {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);
            $allowed_exs = array("jpg", "jpeg", "png");
            
            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                $img_upload_path = 'medicines/' . $new_img_name;
                move_uploaded_file($tmp_name, $img_upload_path);
                foreach ($error as $e) {
                    if (preg_match('/./', $e)) {
                        $errorExists = '';
                    }
                }
                if (!isset($errorExists)) {
                    try {
                        $stmt = $db->prepare("INSERT INTO pharmacy (drug, brand, disease, patient, stock, expiry, filename, family_id) VALUES (?,?,?,?,?,?,?,?)");
                        $stmt->execute([$drugSan, $brandSan, $diseaseSan, $patientSan, $stockSan, $expiry, $new_img_name, $user]);
                        $msg = "$drugSan added";
                        $page = $_SERVER['PHP_SELF'];
                        $sec = "2";
                        header("Refresh: $sec; url=$page");
                    } catch (PDOException $ex) {
                        echo "<h1>Error inserting values in the database</h1>";
                    }
                }
            } else {
                $em = "You can't upload files of this type";
                echo "<h1>" . $em . "</h1>";
            }
        }
    }  
}



//requesting all medicine data from db
try {
    $stmt = $db->prepare("SELECT * FROM pharmacy WHERE family_id = ?");
    $stmt->execute([$user]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "<h1>DB Error! Couldn't retrieve all data from the database</h1>";
}


//editing display message
if (isset($_GET["edit"])) {
    $game = getMedicine($_GET["edit"]);
    $msg = "{$game["title"]} updated.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="app.css">
    <style>
        .home{
          width: 50px;
        }
        .logout{
            width: 50px;
            height: 50px;
        }
    </style>
    <!-- <link rel="stylesheet" href="app.css"> -->
    <title>Pharmacy</title>
</head>
<body>
<nav class = 'navbar'>
       <div class = 'navbar-left'> <img src="../logo.png" class = 'logo'>
        <h1>Home Medicine Stock</h1>
        </div>
        <a class = 'home' href="./logout.php">
                        <img src="logout.png" alt="" srcset="" class = 'logout'>
                    </a>
    </nav>
    <?php
if (isset($msg)) {
    echo "<p class='msg'>", $msg, "</p>";
}
?>
    <!-- <div class = 'catalog'><h1 >Medicine Catalog</h1></div> -->
    <form action="" method="post" enctype="multipart/form-data">
        <table>
            <tr>
    <td colspan="2" <?=!in_array('drug', $error) ? "" : "class='error'"?>><input type="text" name="drug" <?=in_array('drug', $error) ? "value =''" : "value ='$drugSan'";?> placeholder="DRUG NAME" ></td>
    <td <?=!in_array('brand', $error) ? "" : "class='error'"?>><input type="text" name="brand" <?=in_array('brand', $error) ? "value =''" : "value ='$brandSan'";?> placeholder="BRAND NAME" ></td>
    <td <?=!in_array('disease', $error) ? "" : "class='error'"?>><input type="text" name="disease" <?=in_array('disease', $error) ? "value =''" : "value ='$diseaseSan'";?> placeholder="DISEASE" ></td>
    <td <?=!in_array('patient', $error) ? "" : "class='error'"?>><input type="text" name="patient" <?=in_array('patient', $error) ? "value =''" : "value ='$patientSan'";?> placeholder="PATIENT" ></td>
    <td <?=!in_array('stock', $error) ? "" : "class='error'"?>><input type="text" name="stock" <?=in_array('stock', $error) ? "value =''" : "value ='$stockSan'";?> placeholder="STOCK" ></td>
    <td <?=!in_array('expiry', $error) ? "" : "class='error'"?>><input type="date" name="expiry" <?=in_array('expiry', $error) ? "value =''" : "value ='$expiry'";?> placeholder="EXPIRY DATE"></td>
    <td colspan = '2'<?=!in_array('filename', $error) ? "" : "class='error'"?>><input type="file" name="filename" <?=in_array('filename', $error) ? "value ='Upload'" : "value ='$filename'";?>></td>
    <td >
    <button type="submit" id ='add' class="btn" title="Add" name = 'submit'>
                  <i class="fa-solid fa-plus"></i>
                  </button>
                </td>
            </tr>
            <tr>
                <th>#</th>
                <th>DRUG</th>
                <th>BRAND</th>
                <th>DISEASE</th>
                <th>PATIENT NAME</th>
                <th>STOCK</th>
                <th>EXPIRY</th>
                <th>PRODUCT</th>
            </tr>
            <?php
$i = 1;
?>
            <?php foreach ($medicines as $medicine): ?>

                <tr>
                <td><?=$i?></td>
                <td><?=$medicine["drug"]?></td>
                <td><?=$medicine["brand"]?> </td>
                <td><?=$medicine["disease"]?></td>
                <td><?=$medicine["patient"]?></td>
                <td><?=$medicine["stock"]?></td>
                <td><?=$medicine["expiry"]?></td>
                <td><img src = "./medicines/<?=$medicine["filename"]?>"> </td>
                <td class = 'edit-del'>
                    <a class="btn" href="?delete=<?=$medicine["id"]?>" title="Delete"><i class="fa-solid fa-trash-can"></i></a>
                    <a class="btn" href="edit.php?id=<?=$medicine["id"]?>" title="Edit"><i class="fa-solid fa-pen"></i></i></a>
                </td>
            </tr>
            <?php $i++;?>
            <?php endforeach?>
            <tr>
                <td colspan="9">Medicines: <?=$stmt->rowCount()?></td>
            </tr>

        </table>
    </form>

</body>
</html>
