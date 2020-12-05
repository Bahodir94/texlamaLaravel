<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use App\Models\Reports;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Reports::orderBy('created_at', 'DESC')->get();
        return $reports;
    }

    public function one($id)
    {
        $report = Reports::find($id);
        $user = User::find($report['user_id']);
        return response()
                ->json(['user' => $user, 'report' => $report ]);   
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all() , ['img' => 'required', 'desc' => 'required', 'tag' => 'required', ]);
        if ($validator->fails())
        {
            $errorString = implode(",", $validator->messages()
                ->all());

            return response()
                ->json(['success' => false, 'message' => $errorString, ], 401);
        }

        $image = $request->file;
        $name = "images/" . $request->img;

        $realImage = base64_decode($image);

        if (file_put_contents($name, $realImage))
        {
            $rep = new Reports();
            $rep['img'] = $name;
            $rep['desc'] = $request->desc;
            $rep['tag'] = $request->tag;
            $rep['user_id'] = $request->user_id;
            $rep->save();
            // $input = $request->all();
            // $data = Reports::create($input);
            return response()
                ->json(['success' => true, 'message' => "Report is successfully created!", 'data' => $rep, ]);
        }
        return response()->json(['success' => false, 'message' => "Report not created!", 'data' => $data, ]);
    }

    public function update(Request $request, $id)
    {

        $rep = Reports::find($id);

        $validator = Validator::make($request->all() , ['img' => 'required', 'desc' => 'required', 'tag' => 'required', ]);
        if ($validator->fails())
        {
            $errorString = implode(",", $validator->messages()
                ->all());

            return response()
                ->json(['success' => false, 'message' => $errorString, ], 401);
        }
        if ($request->file)
        {
            $image = $request->file;
            $name = "images/" . $request->img;

            $realImage = base64_decode($image);

            if (file_exists($rep['img'])) unlink($rep['img']);

            if (file_put_contents($name, $realImage))
            {
                $rep['img'] = $name;
                $rep['desc'] = $request->desc;
                $rep['tag'] = $request->tag;
                $rep->save();
            }
        }
        else
        {
            $rep['img'] = $request->img;
            $rep['desc'] = $request->desc;
            $rep['tag'] = $request->tag;
            $rep->save();
        }
        return response()
            ->json(['success' => true, 'message' => "Report is successfully updated!", 'data' => $rep, ]);

    }

    public function delete(Request $request, $id)
    {
        $report = Reports::find($id);

        if ($report)
        {
            $report->delete();
            if (file_exists($report['img'])) unlink($report['img']);
            return response()->json(['success' => true, 'message' => "Report deleted successfully!", 'data' => $report, ]);
        }

        return response()->json(['success' => false, 'message' => "Report is not found!", ]);
    }

}

