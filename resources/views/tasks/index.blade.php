<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP - Simple To Do List App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .active {
            background-color: #007bff !important;
            /* Bootstrap primary color */
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>PHP - Simple To Do List App</h1>

        <div class="input-group mb-3">
            <input type="text" id="taskName" class="form-control" placeholder="Add a new task"
                aria-label="Add a new task">
            <div class="input-group-append">
                <button id="addTask" class="btn btn-primary">Add Task</button>
            </div>
        </div>

        <div class="mb-3">
            <button id="showPendingTasks" class="btn btn-secondary">Pending Tasks</button>
            <button id="showAllTasks" class="btn btn-secondary">All Tasks</button>
        </div>

        <ul id="taskList" class="list-group mt-4">
            @include('tasks.partials.task_list')
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Function to load tasks based on status
            function loadTasks(status) {
                $.ajax({
                    url: '{{ route('tasks.index') }}',
                    method: 'GET',
                    data: {
                        status: status
                    },
                    success: function(response) {
                        $('#taskList').html(response);
                    }
                });
            }

            // Function to handle button activation
            function setActiveButton(button) {
                $('#showPendingTasks, #showAllTasks').removeClass('active');
                $(button).addClass('active');
            }

            // Load pending tasks by default on page load
            loadTasks('pending');
            setActiveButton('#showPendingTasks'); // Set "Pending Tasks" as active by default

            $('#showPendingTasks').on('click', function() {
                loadTasks('pending');
                setActiveButton(this);
            });

            $('#showAllTasks').on('click', function() {
                loadTasks('all');
                setActiveButton(this);
            });

            // Add task
            function addTask() {
                var taskName = $('#taskName').val().trim();
                if (taskName === '') {
                    alert('Task name is required');
                    return;
                }

                $.ajax({
                    url: '{{ route('tasks.store') }}',
                    method: 'POST',
                    data: {
                        name: taskName
                    },
                    success: function(response) {
                        var taskHtml = `
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${response.id}">
                        <span>${response.name}</span>
                        <div>
                            <input type="checkbox" class="toggle-complete" data-id="${response.id}" ${response.is_completed ? 'checked' : ''}>
                            <button class="btn btn-danger btn-sm delete-task" data-id="${response.id}">Delete</button>
                        </div>
                    </li>`;

                        // Add task only if it is not already present
                        if ($('#taskList li[data-id="' + response.id + '"]').length === 0) {
                            $('#taskList').append(taskHtml);
                        }

                        $('#taskName').val('');
                    },
                    error: function(xhr) {
                        if (xhr.status === 400) {
                            alert(xhr.responseJSON.error);
                        }
                    }
                });
            }

            $('#addTask').on('click', addTask);

            $('#taskName').on('keypress', function(e) {
                if (e.which == 13) { // Enter key pressed
                    e.preventDefault();
                    addTask();
                }
            });

            // Toggle task completion
            $('#taskList').on('change', '.toggle-complete', function() {
                var taskId = $(this).data('id');
                var checkbox = $(this);

                $.ajax({
                    url: '/tasks/' + taskId,
                    method: 'PATCH',
                    success: function(response) {
                        var taskHtml = `
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${response.id}">
                        <span>${response.name}</span>
                        <div>
                            <input type="checkbox" class="toggle-complete" data-id="${response.id}" ${response.is_completed ? 'checked' : ''}>
                            <button class="btn btn-danger btn-sm delete-task" data-id="${response.id}">Delete</button>
                        </div>
                    </li>`;

                        if ($('#showPendingTasks').hasClass('active')) {
                            // In "Pending Tasks" tab: Remove completed tasks
                            if (response.is_completed) {
                                $('li[data-id="' + response.id + '"]').remove();
                            }
                        } else if ($('#showAllTasks').hasClass('active')) {
                            // In "All Tasks" tab: Replace or add task
                            if ($('#taskList li[data-id="' + response.id + '"]').length > 0) {
                                $('#taskList li[data-id="' + response.id + '"]').replaceWith(
                                    taskHtml);
                            } else {
                                $('#taskList').append(taskHtml);
                            }
                        }
                    },
                    error: function(xhr) {
                        alert('Error updating task.');
                    }
                });
            });

            // Delete task
            $('#taskList').on('click', '.delete-task', function() {
                var taskId = $(this).data('id');
                if (!confirm('Are you sure you want to delete this task?')) {
                    return;
                }
                $.ajax({
                    url: '/tasks/' + taskId,
                    method: 'DELETE',
                    success: function() {
                        $('li[data-id="' + taskId + '"]').remove();
                    },
                    error: function(xhr) {
                        alert('Error deleting task.');
                    }
                });
            });
        });
    </script>
</body>

</html>
