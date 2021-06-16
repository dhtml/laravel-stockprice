<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use File;
use Carbon\Carbon;

class StockController extends Controller
{

    /**
    * Add single stock item
    *
    * @param Request $request   The request object
    */
    public function addItem(Request $request)
    {
        $form_data = $request->only('name', 'quantity', 'price');
        $form_data['time'] = time();
        $form_data['total'] = $request->quantity * $request->price;

        $fileName = $form_data['time']. '_stock.json';
        File::put(public_path('/stocks/'.$fileName), json_encode($form_data));

        return response()->json([
              'success' => true,
            ]);
    }


    /**
    * Edit single stock item
    *
    * @param Request $request   The request object
    */
    public function editItem(Request $request)
    {
        $form_data = $request->only('name', 'quantity', 'time', 'price');
        $form_data['total'] = $request->quantity * $request->price;

        $file_name = $request->path;
        $file_path = public_path('/stocks/'.$file_name);

        File::put($file_path, json_encode($form_data));

        return response()->json([
              'success' => true,
            ]);
    }

    /**
    * Delete single stock item
    *
    * @param Request $request   The request object
    */
    public function deleteStock(Request $request)
    {
        $file_name = $request->path;
        $file_path = public_path('/stocks/'.$file_name);

        if (File::exists($file_path)) {
            File::delete($file_path);
        }

        return response()->json([
            'success' => true,
          ]);
    }

    /**
    * Delete entire stock items
    *
    * @param Request $request   The request object
    */
    public function deleteAllStock(Request $request)
    {
        $files = glob(public_path('/stocks/').'*.json');

        foreach ($files as $file) {
            File::delete($file);
        }
        
        return response()->json([
            'success' => true,
          ]);
    }

    /**
    * Get single stock item
    *
    * @param Request $request   The request object
    */
    public function getStock(Request $request)
    {
        $file = public_path('/stocks/').$request->path;

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file));
            $data->path = $request->path;
            return response()->json([
          'success' => true,
          'data'=>$data,
        ]);
        } else {
            return response()->json([
          'success' => false,
        ], 404);
        }
    }

    /**
    * Fetch All Stock Items
    */
    public function fetchAll()
    {
        $stockList = ['rows'=>[],"total"=>0];

        $files = glob(public_path('/stocks/').'*.json');

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

        //sort so that most recent file is at the bottom of the list
        usort($stockList['rows'], function ($a, $b) {
            return $a->time - $a->time;
        });


        return response()->json($stockList);
    }
}
