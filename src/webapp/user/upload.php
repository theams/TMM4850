<?php
$target_dir = "web/profilepictures/";
$target_dir = $target_dir . basename( $_FILES["uploadFile"]["name"]);
$uploadOk=1;
if (file_exists($target_dir . $_FILES["uploadFile"]["name"])) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
if ($uploadFile_size > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
if ($uploadFile_type == "text/php") {
    echo "Sorry, no PHP files allowed.";
    $uploadOk = 0;
}
if (!($uploadFile_type == "image/jpg")) {
    echo "Sorry, only jpg files are allowed.";
    $uploadOk = 0;
}
if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $target_dir)) {
    echo "The file ". basename( $_FILES["uploadFile"]["name"]). " has been uploaded.";
} else {
    echo "Sorry, there was an error uploading your file.";
}

?>