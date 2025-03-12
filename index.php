<?php
include 'koneksi.php';

// Tambah data
if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $task_date = $_POST['task_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $query = "INSERT INTO tasks (title, task_date, start_time, end_time, status) 
              VALUES ('$title', '$task_date', '$start_time', '$end_time', 'pending')";
    mysqli_query($conn, $query);
    header("Location: index.php");
}

// Hapus data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header("Location: index.php");
}

// Tandai selesai
if (isset($_GET['done'])) {
    $id = $_GET['done'];
    mysqli_query($conn, "UPDATE tasks SET status='done' WHERE id=$id");
    header("Location: index.php");
}

// Ambil data dari database
$query = "SELECT * FROM tasks ORDER BY task_date, start_time";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <title>To-Do List</title>
</head>
<body>

    <h2>To-Do List</h2>
    <!-- Form Tambah Data -->
    <form method="POST">
        <input type="text" name="title" placeholder="Judul" required>
        <input type="date" name="task_date" required>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <button type="submit" name="add_task">Tambah</button>
    </form>

    <!-- Tabel Daftar Tugas -->
    <table>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Aksi</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            // Ubah format tanggal menjadi DD-MM-YYYY
            $formatted_date = date("d-m-Y", strtotime($row['task_date']));
            
            // Format jam tetap dalam 24 jam
            $start_time = date("H:i", strtotime($row['start_time']));
            $end_time = date("H:i", strtotime($row['end_time']));

            $status_class = ($row['status'] == 'done') ? "done" : "";
            echo "<tr>
                <td>{$no}</td>
                <td class='$status_class'>{$row['title']}</td>
                <td>{$formatted_date}</td>
                <td>{$start_time} - {$end_time}</td>
                <td>
                    <a href='index.php?done={$row['id']}' class='btn done-btn'>Selesai</a>
                    <a href='index.php?delete={$row['id']}' class='btn delete' onclick='return confirm(\"Hapus tugas ini?\")'>Hapus</a>
                </td>
            </tr>";
            $no++;
        }
        ?>
    </table>

</body>
</html>

<?php
// Tutup koneksi database
mysqli_close($conn);
?>
