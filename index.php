<?php
session_start();

require_once 'config/koneksi.php';
require_once 'config/functions.php';

// Ambil daftar tugas
$tasksResponse = getTasks($koneksi);
$tasks = $tasksResponse['tasks'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap"></noscript>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
</head>
<body>
    <div class="container">
        <header>
            <h1 class="title">To do List</h1>
            <nav class="date">
                <span class="material-symbols-outlined">calendar_today</span>
                <time datetime="<?= date('Y-m-d') ?>"><?= date("l, d M Y") ?></time>
            </nav>
        </header>

        <main>
           
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="flash-message flash-<?= $_SESSION['flash_type'] ?>">
                    <?= htmlspecialchars($_SESSION['flash_message']) ?>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                </div>
            <?php endif; ?>

            <section class="task-input-section">
                <form id="taskform" action="config/functions.php" method="post">
                    <div class="task-time-container">
                        <div class="task-date-input">
                            <label for="taskDate">Pilih Tanggal</label>
                            <input type="date" id="taskDate" name="taskDate" required>
                        </div>
                        <div class="task-time-input">
                            <label for="taskTime">Pilih Waktu</label>
                            <input type="time" id="taskTime" name="taskTime" required>
                        </div>
                    </div>
                    
                    <div class="task-add-container">
                        <input type="text" name="task" id="taskinput" placeholder="Tambah Tugas Baru" required>
                        <button type="submit" name="add" value="1">Tambah</button>
                    </div>
                </form>
            </section>

            <hr>

            <section id="taskcontainer">
                <h2>Daftar Tugas</h2>
                <?php if (empty($tasks)): ?>
                    <p>Belum ada tugas. Ayo tambahkan tugas pertamamu!</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <article class="task-item <?= $task['taskstatus'] == 'close' ? 'done' : '' ?>">
                            <div class="task-content">
                                <h3 class="task-label"><?= htmlspecialchars($task['tasklabel']) ?></h3>
                                <div class="task-metadata">
                                    <time datetime="<?= $task['task_date'] ?? '' ?>">
                                        <?= $task['task_date'] ?? 'Tanpa Tanggal' ?>
                                    </time>
                                    <span class="task-time">
                                        <?= $task['task_time'] ?? 'Tanpa Waktu' ?>
                                    </span>
                                    <span class="task-progress">
                                        <?= htmlspecialchars($task['task_progress']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <a href="config/functions.php?done=<?= $task['taskid'] ?>&status=<?= $task['taskstatus'] ?>" 
                                   class="task-status-toggle">
                                    <input 
                                        type="checkbox" 
                                        <?= $task['taskstatus'] == 'close' ? 'checked' : '' ?>
                                    >
                                </a>
                                <a href="config/functions.php?delete=<?= $task['taskid'] ?>" 
                                   class="delete-task" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                    <i class="material-symbols-outlined">delete</i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="assets/js/script.js?v=<?= time() ?>"></script>
</body>
</html>