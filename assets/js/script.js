document.addEventListener('DOMContentLoaded', () => {
    const taskForm = document.getElementById('taskform');
    const taskContainer = document.getElementById('taskcontainer');

    // Event Delegation untuk Checkbox dan Delete Button
    taskContainer.addEventListener('click', (event) => {
        const checkbox = event.target.closest('input[type="checkbox"]');
        const deleteButton = event.target.closest('.delete-task');

        if (checkbox) {
            handleTaskStatusToggle(checkbox);
        }

        if (deleteButton) {
            handleTaskDelete(deleteButton);
        }
    });

    // Validasi Form Tambah Tugas
    taskForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const taskInput = document.getElementById('taskinput');
        const taskDateInput = document.getElementById('taskDate');
        const taskTimeInput = document.getElementById('taskTime');

        if (validateTaskInput(taskInput, taskDateInput, taskTimeInput)) {
            submitTask(taskInput.value, taskDateInput.value, taskTimeInput.value);
        }
    });

    function validateTaskInput(taskInput, dateInput, timeInput) {
        const errorClass = 'input-error';
        let isValid = true;

        // Reset previous error states
        [taskInput, dateInput, timeInput].forEach(input => {
            input.classList.remove(errorClass);
        });

        if (taskInput.value.trim() === '') {
            taskInput.classList.add(errorClass);
            isValid = false;
        }

        if (dateInput.value === '') {
            dateInput.classList.add(errorClass);
            isValid = false;
        }

        if (timeInput.value === '') {
            timeInput.classList.add(errorClass);
            isValid = false;
        }

        return isValid;
    }

    function handleTaskStatusToggle(checkbox) {
        const taskId = checkbox.dataset.taskid;
        const taskItem = checkbox.closest('.task-item');

        // Kirim permintaan AJAX untuk mengubah status
        fetch(`index.php?done=${taskId}&status=${checkbox.checked ? 'open' : 'close'}`, {
            method: 'GET'
        })
        .then(response => {
            if (response.ok) {
                // Toggle class untuk visual feedback
                taskItem.classList.toggle('done', checkbox.checked);
            } else {
                // Kembalikan checkbox ke kondisi semula jika gagal
                checkbox.checked = !checkbox.checked;
                showNotification('Gagal mengubah status tugas', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkbox.checked = !checkbox.checked;
            showNotification('Terjadi kesalahan', 'error');
        });
    }

    function handleTaskDelete(deleteButton) {
        const taskId = deleteButton.closest('.task-item').querySelector('.delete-task').getAttribute('href').split('=')[1];
        const taskItem = deleteButton.closest('.task-item');
    
        // Kirim permintaan AJAX untuk menghapus
        fetch(`index.php?delete=${taskId}`, {
            method: 'GET'
        })
        .then(response => {
            if (response.ok) {
                // Animasi hapus
                taskItem.classList.add('task-item-remove');
                taskItem.addEventListener('animationend', () => {
                    taskItem.remove();
                    checkEmptyTaskList();
                    showNotification('Tugas berhasil dihapus', 'success');
                });
            } else {
                showNotification('Gagal menghapus tugas', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        });
    }

    function submitTask(taskText, taskDate, taskTime) {
        const formData = new FormData();
        formData.append('task', taskText);
        formData.append('taskDate', taskDate);
        formData.append('taskTime', taskTime);
        formData.append('add', '1');

        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                // Reset form
                taskForm.reset();
                
                // Refresh daftar tugas
                return fetch('index.php');
            } else {
                throw new Error('Gagal menambahkan tugas');
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update daftar tugas tanpa reload penuh
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newTaskContainer = tempDiv.querySelector('#taskcontainer');
            
            if (newTaskContainer) {
                taskContainer.innerHTML = newTaskContainer.innerHTML;
            }
            
            showNotification('Tugas berhasil ditambahkan', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal menambahkan tugas', 'error');
        });
    }

    function checkEmptyTaskList() {
        const tasks = taskContainer.querySelectorAll('.task-item');
        if (tasks.length === 0) {
            const emptyMessage = document.createElement('p');
            emptyMessage.textContent = 'Belum ada tugas. Ayo tambahkan tugas pertamamu!';
            emptyMessage.classList.add('empty-task-message');
            taskContainer.appendChild(emptyMessage);
        }
    }

    function showNotification(message, type = 'info') {
        // Buat elemen notifikasi
        const notification = document.createElement('div');
        notification.classList.add('notification', `notification-${type}`);
        notification.textContent = message;

        // Tambahkan ke body
        document.body.appendChild(notification);

        // Hapus notifikasi setelah beberapa detik
        setTimeout(() => {
            notification.classList.add('notification-exit');
            notification.addEventListener('animationend', () => {
                notification.remove();
            });
        }, 3000);
    }

    // Tambahkan gaya untuk notifikasi di CSS
    const notificationStyle = document.createElement('style');
    notificationStyle.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 8px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .notification-success {
            background-color: #4ade80;
        }

        .notification-error {
            background-color: #ff6b6b;
        }

        .notification-exit {
            animation: slideOut 0.3s ease forwards;
        }

        @keyframes slideIn {
            from { opacity: 0; top: 0; }
            to { opacity: 1; top: 20px; }
        }

        @keyframes slideOut {
            from { opacity: 1; top: 20px; }
            to { opacity: 0; top: 0; }
        }

        .input-error {
            border: 2px solid #ff6b6b;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .task-item-remove {
            animation: fadeOut 0.3s ease forwards;
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.9); }
        }
    `;
    document.head.appendChild(notificationStyle);
});