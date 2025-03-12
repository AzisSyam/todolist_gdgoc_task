<?php
$host = "localhost";
$user = "root"; // Ganti jika pakai user lain
$password = ""; // Ganti jika ada password
$database = "todo_list_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
