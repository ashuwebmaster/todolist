@foreach ($tasks as $task)
    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $task->id }}">
        <span>{{ $task->name }}</span>
        <div>
            <input type="checkbox" class="toggle-complete" data-id="{{ $task->id }}"
                {{ $task->is_completed ? 'checked' : '' }}>
            <button class="btn btn-danger btn-sm delete-task" data-id="{{ $task->id }}">Delete</button>
        </div>
    </li>
@endforeach
