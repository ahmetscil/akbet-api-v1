<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Project;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProjectController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $query = Project::query();

        // 0: passive, 1: active, 2: complete
        $query->join('company','company.id','=','project.company');
        if ($request->has('search'))
            $query->where('title', 'like', '%' . $request->query('search') . '%');
        if ($request->has('company'))
            $query->where('company', '=', $request->query('company'));
        if ($request->has('status'))
            $query->where('project.status', '=', $request->query('status'));

        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects, 'company.name as companyName');
        } else {
            $query->select('project.*','company.name as companyName');
        }

        $length = count($query->get());
        $data = $query->offset($offset)->limit($limit)->get();
        $data->each->setAppends(['fullAddress']);

        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Project Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'company' => 'required',
            'code' => 'nullable',
            'title' => 'required',
            'description' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'country' => 'nullable',
            'logo' => 'nullable',
            'status' => 'required',
            'started_at' => 'nullable',
            'ended_at' => 'nullable',
            'created_at' => 'nullable',
            'updated_at' => 'nullable',
            'token' => 'unique:project,token'
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new Project();
        $data->company = request('company');
        $data->code = request('code');
        $data->title = request('title');
        $data->description = request('description');
        $data->address = request('address');
        $data->city = request('city');
        $data->country = request('country');
        $data->logo = request('logo');
        $data->status = request('status');
        $data->started_at = request('started_at');
        $data->ended_at = request('ended_at');
        $data->created_at = request('created_at');
        $data->updated_at = request('updated_at');
        $data->token = str_random(64);
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'project';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 1;
            $log->info = 'Project '.$data->id.' Created for the Company '.$data->company;
            $log->save();
            return $this->apiResponse(ResaultType::Success, $data, 'Project Created', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Project not saved', 500);
        }
    }

    public function show($token)
    {
        $data = Project::where('token','=',$token)->first();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Project Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Project Not Found', 404);
        }
    }

    public function update(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'nullable',
            'title' => 'nullable',
            'description' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'country' => 'nullable',
            'logo' => 'nullable',
            'status' => 'nullable',
            'started_at' => 'nullable',
            'ended_at' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = Project::where('token','=',$token)->first();

        if ($data) {
            if (request('code') != '') {
                $data->code = request('code');
            }
            if (request('title') != '') {
                $data->title = request('title');
            }
            if (request('description') != '') {
                $data->description = request('description');
            }
            if (request('address') != '') {
                $data->address = request('address');
            }
            if (request('city') != '') {
                $data->city = request('city');
            }
            if (request('country') != '') {
                $data->country = request('country');
            }
            if (request('logo') != '') {
                $data->logo = request('logo');
            }
            if (request('status') != '') {
                $data->status = request('status');
            }
            if (request('started_at') != '') {
                $data->started_at = request('started_at');
            }
            if (request('ended_at') != '') {
                $data->ended_at = request('ended_at');
            }
            $data->status = request('status');
            $data->save();

            if ($data) {
                $log = new Log();
                $log->area = 'project';
                $log->areaid = $data->id;
                $log->user = Auth::id();
                $log->ip = \Request::ip();
                $log->type = 2;
                $log->info = 'Project '.$data->id.' Updated in Company '.$data->company;
                $log->save();

                return $this->apiResponse(ResaultType::Success, $data, 'Project Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Project not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Warning, null, 'Data not found', 404);
        }
    }

    public function destroy($token)
    {
        $data = Project::where('token','=',$token)->first();
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Project Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

