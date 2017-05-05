@extends('layouts.app')

@section('content')

<div class="panel-body">
    <!-- view errors -->
    @include('errors.errors')

    <!-- form new task -->
    <form action="{{ url('task') }}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <!-- Name task -->
        <div class="form-group">
            <label for="task" class="col-sm-3 control-label">Task</label>

            <div class="col-sm-6">
                <input type="text" name="name" id="task-name" class="form-control">
            </div>
        </div>

        <!-- Button add task -->
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-plus"></i> Add task
                </button>
            </div>
        </div>
    </form>
</div>

@if (count($tasks) > 0)
<div class="panel panel-default">
    <div class="panel-heading">
        Current tasks
    </div>

    <div class="panel-body">
        <table class="table table-striped task-table">

            <!-- table head -->
            <thead>
            <th>Task</th>
            <th>&nbsp;</th>
            </thead>

            <!-- table body -->
            <tbody>
                @foreach ($tasks as $task)
                <tr>
                    <!-- Task Name -->
                    <td class="table-text">
                        <div>{{ $task->name }}</div>
                    </td>

                    <td>
                        <form action="{{ url('task/'.$task->id) }}" method="POST">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}

                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Удалить
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection