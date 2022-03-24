<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Section;
use App\Project;
use App\SectionInfo;
use App\SectionInfoLabel;
use App\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\SectionResource;

use Illuminate\Http\Request;
use Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class SectionController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $type = $request->type ? $request->type : null;
        $query = Section::query();
        $length = 1;
        if ($request->has('type')){
            $query->where('type', $request->query('type'));
        } else {
            $length = count($query->get());
        }
        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('status'))
            $query->where('section.status', '=', $request->query('status'));


        if ($request->has('start')) {
            $start = $request->query('start');
            $end = $request->query('end');
            $query->whereBetween('created_at',[$start,$end]);
        }


        if ($request->has('project')) {
            $project = Project::where('project.token','=',$request->query('project'))->first();
            $query->where('section.project', $project->id);
        }
        $query->join('project','project.id','=','section.project');
        $query->join('users','users.id','=','section.user');
        $query->join('company','company.id','=','project.company');
        $query->select('section.*','project.title as projectName','project.token','users.name as userName','company.name as companyName', 'company.id as company');
        $data = $query->offset($offset)->limit($limit)->get();

        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Success, null, 'Content Not Found', 0, 202);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project' => 'required',
            'user' => 'required',
            'title' => 'required',
            'description' => 'nullable',
            'status' => 'required',
            'started_at' => 'nullable',
            'ended_at' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new Section();
        $data->project = request('project');
        $data->user = request('user');
        $data->title = request('title');
        $data->description = request('description');
        $data->status = request('status');
        $data->started_at = request('started_at');
        $data->ended_at = request('ended_at');
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'section';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 1;
            $log->info = 'Section '.$data->id.' Created for the Project '.$data->project;
            $log->save();

            return $this->apiResponse(ResaultType::Success, $data, 'Section Added', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Section not Added', 500);
        }
    }

    public function show($id)
    {
        $data = Section::where('section.id','=', $id)->first();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Section Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable',
            'description' => 'nullable',
            'status' => 'nullable',
            'started_at' => 'nullable',
            'ended_at' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = Section::find($id);
        if (request('title')) {
            $data->title = request('title');
        }
        if (request('description')) {
            $data->description = request('description');
        }
            $data->status = request('status');
        if (request('started_at')) {
            $data->started_at = request('started_at');
        }
        if (request('ended_at')) {
            $data->ended_at = request('ended_at');
        }
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'section';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 2;
            $log->info = 'Section '.$data->id.' Updated in Project '.$data->project;
            $log->save();

            return $this->apiResponse(ResaultType::Success, $data, 'Section Updated', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content not updated', 500);
        }
    }

    public function destroy($id)
    {
        $data = Section::find($id);
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Section Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

