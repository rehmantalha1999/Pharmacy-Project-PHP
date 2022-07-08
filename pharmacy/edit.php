<?php
require "db.php";
$id = $_GET["id"];
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
$error = array();
$drugSan = '';
$brandSan = '';
$diseaseSan = '';
$patientSan = '';
$stockSan = '';
$filename = '';
$expiry = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['filename']) && isset($_POST['submit'])) {
    extract($_POST);
    $error = array();
    array_push($error, strlen(trim($drug)) === 0 ? "drug" : "");
    array_push($error, strlen(trim($brand)) === 0 ? "brand" : "");
    array_push($error, strlen(trim($disease)) === 0 ? "disease" : "");
    array_push($error, strlen(trim($patient)) === 0 ? "patient" : "");
    array_push($error, strlen(trim($stock)) === 0 ? "stock" : "");
    array_push($error, strlen(trim($expiry)) === 0 ? "expiry" : "");
    // array_push($error, strlen(trim($filename)) === 0 ? "filename" : "");
    array_push($error, filter_var($stock, FILTER_VALIDATE_INT) ? "" : "stock");
    $drugSan = filter_var($drug, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $brandSan = filter_var($brand, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $diseaseSan = filter_var($disease, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $patientSan = filter_var($patient, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stockSan = filter_var($stock, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $img_name = $_FILES['filename']['name'];
    // var_dump($_FILES['filename']['name']);
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
                        $drugImage = getMedicineImage($id);
                        unlink("medicines/".$drugImage['filename']);
                        $stmt = $db->prepare("UPDATE pharmacy SET drug =?, brand=?, disease=?, patient=?, stock=?, expiry=?, filename=? WHERE id = ?");
                        $stmt->execute([$drugSan, $brandSan, $diseaseSan, $patientSan, $stockSan, $expiry, $new_img_name, $id]);
                        $msg = "$drugSan edited";
                        $page = "pharmacy.php";
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

//getting stored medicine info to edit

try {
    $stmt = $db->prepare("SELECT * FROM pharmacy WHERE id = ?");
    $stmt->execute([$id]);
    $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "<h1>Couldn't retrieve stored medicine info with id " . $id . " from the db</h1>";
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
    <title>Pharmacy</title>
</head>
<body>
<nav class = 'navbar'>
       <div class = 'navbar-left'> <img src="../logo.png" class = 'logo'>
        <h1>Home Medicine Stock</h1>
        </div>
        <a class = 'home' href="pharmacy.php">&#x1f3e0;
         </a>
    </nav>
    <?php
if (isset($msg)) {
    echo "<p class='msg'>", $msg, "</p>";
}
?>
    <h1 class = 'catalog'>Edit Medicine Information</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td class = 'edit-td'>DRUG</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('drug', $error) ? "" : "class='error'"?> type="text" name="drug"  value ='<?=$medicine['drug']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>BRAND</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('brand', $error) ? "" : "class='error'"?> type="text" name="brand"  value ='<?=$medicine['brand']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>DISEASE</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('disease', $error) ? "" : "class='error'"?> type="text" name="disease" value ='<?=$medicine['disease']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>patient</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('patient', $error) ? "" : "class='error'"?> type="text" name="patient" value ='<?=$medicine['patient']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>STOCK</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('stock', $error) ? "" : "class='error'"?> type="text" name="stock" value ='<?=$medicine['stock']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>EXPIRY</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('expiry', $error) ? "" : "class='error'"?> type="date" name="expiry" value ='<?=$medicine['expiry']?>'>
                </td>
            </tr>
            <tr>
                <td class = 'edit-td'>PRODUCT</td>
                <td class = 'edit-td'>
                    <input <?=!in_array('filename', $error) ? "" : "class='error'"?> type="file" name="filename">
                </td>
            </tr>
            <tr>
                <td colspan="2" class="center">
                   <button type="submit" class="btn" name = 'submit'>
                   <i class="fa-solid fa-rotate-right"></i>
                   </button>
                </td>
            </tr>

        </table>
    </form>


</body>
</html>

