<?php
// Kết nối tới CSDL web_nha_hang
$conn = new mysqli("localhost", "root", "", "web_nha_hang");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>