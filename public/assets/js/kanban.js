document.addEventListener('DOMContentLoaded', () => {
    const tasks = document.querySelectorAll('.task-card');
    const columns = document.querySelectorAll('.kanban-column');

    tasks.forEach(task => {
        task.addEventListener('dragstart', dragStart);
        task.addEventListener('dragend', dragEnd);
    });

    columns.forEach(column => {
        column.addEventListener('dragover', dragOver);
        column.addEventListener('drop', drop);
    });

    function dragStart(e) {
        e.target.classList.add('dragging');
        e.dataTransfer.setData('text/plain', e.target.dataset.taskId);
    }

    function dragEnd(e) {
        e.target.classList.remove('dragging');
    }

    function dragOver(e) {
        e.preventDefault();
    }

    function drop(e) {
        e.preventDefault();
        const taskId = e.dataTransfer.getData('text/plain');
        const newStatus = e.target.closest('.kanban-column').dataset.status;
        
        updateTaskStatus(taskId, newStatus)
            .then(response => {
                if (response.success) {
                    const task = document.querySelector(`[data-task-id="${taskId}"]`);
                    e.target.closest('.kanban-column').appendChild(task);
                }
            });
    }

    async function updateTaskStatus(taskId, status) {
        const response = await fetch('/kanban/updateStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `taskId=${taskId}&status=${status}`
        });
        return response.json();
    }
});
