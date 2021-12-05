<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Platform\Traits\ApiResponser;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * list of pending task
     *
     * all business logic are writen in controller but it can be refactored using service class. It can be done at latter stage
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['children' => function ($query) use ($request) {

            if ($request->has('due_date')) {
                $due_date = $request->due_date;
                switch ($due_date) {
                    case 'Today':
                        $query->dueToday();
                        break;
                    case 'This Week':
                        $query->dueThisWeek();
                        break;
                    case 'Next Week':
                        $query->dueNextWeek();
                        break;
                    case 'Overdue':
                        $query->overDue();
                        break;
                }
            }
        }]);

        if ($request->has('due_date')) {
            $due_date = $request->due_date;
            switch ($due_date) {
                case 'Today':
                    $tasks->dueToday();
                    break;
                case 'This Week':
                    $tasks->dueThisWeek();
                    break;
                case 'Next Week':
                    $tasks->dueNextWeek();
                    break;
                case 'Overdue':
                    $tasks->overDue();
                    break;
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $tasks->where('title', 'LIKE', "%{$search}%");
        }
        if ($request->has('perpage')) {
            $tasks = $tasks->paginate($request->perpage);
        } else {
            $tasks = $tasks->paginate(10);
        }


        return $this->success(['tasks' => new TaskResource($tasks)], 'List of tasks', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rule = [
            'title'      => 'required|string|max:255',
            'due_date'  => 'required|date',
        ];

        if ($request->parent_id) {
            $rule['parent_id'] = 'integer|exists:tasks,id';
        }


        $validated =  Validator::make($request->all(), $rule);
        if ($validated->fails()) {
            $validation_error =  $validated->errors()->toArray();
            return $this->error('Valication error', 400, ['errors' => $validation_error]);
        }

        $data = [
            'title' => $request->title,
            'due_date' => date('Y-m-d', strtotime($request->due_date)),
        ];

        if ($request->parent_id) {
            $data['parent_id'] = $request->parent_id;
        }
        $task = Task::create($data);

        return $this->success(['task' => new TaskResource($task)], 'Created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task =  Task::find($id);
        if (!$task) {
            return $this->error('Invalid  task id', 400, ['errors' => ['id' => 'Invalid  task id']]);
        }
        $task->delete();
        return $this->success([], 'Successfully deleted');
    }

    /**
     * Change task status
     */

    public function changeStatus(Request $request)
    {
        $rule = [
            'task_id'      => 'required|exists:tasks,id',
            'status_id'      => 'required|exists:task_statuses,id',
        ];

        $validated =  Validator::make($request->all(), $rule);
        if ($validated->fails()) {
            $validation_error =  $validated->errors()->toArray();
            return $this->error('Valication error', 400, ['errors' => $validation_error]);
        }

        $task =  Task::with('children')->find($request->task_id);
        $task->status = $request->status_id;
        $task->children()->update(['status' => $request->status_id]);
        $task->save();

        return $this->success(['task' => new TaskResource($task)], 'Successfully updated', 201);
    }


    /**
     * get list of pending task
     */
    public function pendingList(Request $request)
    {
        $tasks = Task::with(['children' => function ($query) {
            $query->pending();
        }])->mainTask()->pending(); //->paginate($request->perpage);

        if ($request->has('perpage')) {
            $tasks = $tasks->paginate($request->perpage);
        } else {
            $tasks = $tasks->paginate(10);
        }
        return $this->success(['tasks' => new TaskResource($tasks)], 'List of tasks', 200);
    }
}
