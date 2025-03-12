<?php
// config/functions.php

// Pastikan koneksi database sudah di-include
require_once 'koneksi.php';

function handleTaskOperations($koneksi) {
    // Proses tambah tugas
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
        return addTask($koneksi);
    }

    // Proses hapus tugas
    if (isset($_GET['delete'])) {
        return deleteTask($koneksi, $_GET['delete']);
    }

    // Proses ubah status tugas
    if (isset($_GET['done'])) {
        return updateTaskStatus($koneksi, $_GET['done'], $_GET['status']);
    }

    // Ambil daftar tugas default
    return getTasks($koneksi);
}

function addTask($koneksi) {
    // Validasi input
    $task = mysqli_real_escape_string($koneksi, $_POST['task'] ?? '');
    $taskDate = mysqli_real_escape_string($koneksi, $_POST['taskDate'] ?? '');
    $taskTime = mysqli_real_escape_string($koneksi, $_POST['taskTime'] ?? '');

    // Cek apakah input kosong
    if (empty($task) || empty($taskDate) || empty($taskTime)) {
        return [
            'success' => false,
            'message' => 'Semua bidang harus diisi'
        ];
    }

    // Query untuk menambah tugas
    $query = "INSERT INTO tasks (tasklabel, taskstatus, task_date, task_time) 
              VALUES ('$task', 'open', '$taskDate', '$taskTime')";
    
    $result = mysqli_query($koneksi, $query);

    return [
        'success' => $result ? true : false,
        'message' => $result ? 'Tugas berhasil ditambahkan' : 'Gagal menambahkan tugas'
    ];
}

function deleteTask($koneksi, $taskId) {
    // Validasi input (pastikan $taskId adalah angka)
    if (!is_numeric($taskId)) {
        return [
            'success' => false,
            'message' => 'ID tugas tidak valid'
        ];
    }

    // Menggunakan prepared statement untuk keamanan
    $query = "DELETE FROM tasks WHERE taskid = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $taskId);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        // Redirect ke index.php dengan pesan sukses
        header("Location: ../index.php?message=deleted");
        exit();
    } else {
        return [
            'success' => false,
            'message' => 'Gagal menghapus tugas'
        ];
    }
}


function updateTaskStatus($koneksi, $taskId, $currentStatus) {
    // Validasi input
    $taskId = mysqli_real_escape_string($koneksi, $taskId);
    $currentStatus = mysqli_real_escape_string($koneksi, $currentStatus);

    // Tentukan status baru
    $newStatus = ($currentStatus == 'open') ? 'close' : 'open';
    
    // Tentukan progress
    $newProgress = ($newStatus == 'close') ? 'Selesai' : 'Belum Selesai';

    // Query untuk memperbarui status tugas
    $query = "UPDATE tasks SET taskstatus = '$newStatus', task_progress = '$newProgress' WHERE taskid = '$taskId'";
    
    $result = mysqli_query($koneksi, $query);

    return [
        'success' => $result ? true : false,
        'message' => $result ? 'Status tugas berhasil diperbarui' : 'Gagal memperbarui status tugas',
        'newStatus' => $newStatus,
        'newProgress' => $newProgress
    ];
}

function getTasks($koneksi) {
    // Query untuk mengambil semua tugas
    $query = "SELECT * FROM tasks ORDER BY taskid DESC";
    $result = mysqli_query($koneksi, $query);

    // Konversi hasil query ke array
    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }

    return [
        'success' => true,
        'tasks' => $tasks
    ];
}

// Fungsi untuk menangani respons
function handleResponse($response) {
    // Jika ini adalah permintaan AJAX, kembalikan JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Untuk permintaan biasa, gunakan session untuk flash message
    if (!session_id()) {
        session_start();
    }

   
}

// Jalankan operasi dan tangani respons
$response = handleTaskOperations($koneksi);
handleResponse($response);