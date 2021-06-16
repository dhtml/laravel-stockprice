<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use File;
use Carbon\Carbon;

class StockController extends Controller
{
    public function addItem(Request $request)
    {
        if ($request->isMethod('post')) {
            $form_data = $request->only('name', 'quantity', 'price');
            $form_data['time'] = time();
            $form_data['total'] = $request->quantity * $request->price;

            $fileName = $form_data['time']. '_stock.json';
            File::put(public_path('/stocks/'.$fileName), json_encode($form_data));

            $this->validate($request, [
            'name' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            ]);

            return response()->json([
              'success' => true,
            ]);
        }
    }

    /**
    * Fetch All Stock Items
    */
    public function fetchAll()
    {
        $stockList = ['rows'=>[],"total"=>0];

        $files = glob(public_path('/stocks/').'*.json');
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        $pos = 1;
        foreach ($files as $file) {
            $stockItem = json_decode(file_get_contents($file));
            $carbon = Carbon::createFromTimestamp($stockItem->time);
            $stockItem->datetime = $carbon->toDayDateTimeString();
            $stockItem->pos = $pos;
            $stockItem->path = basename($file);
            $stockList['rows'][] = $stockItem;
            $stockList['total'] += $stockItem->total;
            $pos++;
        }

        return response()->json($stockList);
    }

    public function editTask(Request $request)
    {
        $task_id = $request->id;
        $task = Task::findOrFail($task_id);
        $project = $task->project;
        if ($request->isMethod('post')) {
            if ($task->name!=$request->name) {
                //only validate if a different name is being sent
                $this->validate($request, [
                'name' => 'required|max:100|unique:projects',
                'priority' => 'required|int',
                'datetime' => 'required|max:100',
              ]);
            }
            if ($request->action=="delete") {
                Task::where('id', $task->id)->delete();
                flash('Your task was deleted successfully')->info()->important();
                return redirect()->route('view-project', ['id'=>$project->id]);
            }
            Task::where('id', $task->id)->update(['name'=>$request->input('name'),'priority'=>$request->input('priority'),'datetime'=>$request->input('datetime')]);
            flash('Your task was updated successfully')->info()->important();
            return back();
        }
        return view('task.edit', ['task'=>$task]);
    }
}
