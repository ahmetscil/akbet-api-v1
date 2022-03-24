<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Devices;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DevicesController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $query = Devices::query();

        $query->join('users','users.id','=','devices.user');
        $query->join('mix','mix.id','=','devices.mix');
        $query->join('section','section.id','=','devices.section');
        $query->join('project','project.id','=','section.project');
        $query->join('company','company.id','=','project.company');

        if ($request->has('search'))
            $query->where('title', 'like', '%' . $request->query('search') . '%');

        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects,'users.name as userName', 'mix.titlle as mixTitle', 'section.title as sectionTitle', 'project.title as projectTitle', 'company.name as companyName');
        } else {
            $query->select('devices.*','users.name as userName', 'mix.title as mixTitle', 'section.title as sectionTitle', 'project.title as projectTitle', 'company.name as companyName');
        }

        if ($request->has('section')) {
            $query->where('section', $request->query('section'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }


            $length = count($query->get());
            $data = $query->offset($offset)->limit($limit)->get();
            $data->each->setAppends(['lastData']);
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Devices Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mix' => 'required',
            'section' => 'required',
            'deveui' => 'unique:devices,deveui',
            'user' => 'required',
            'type' => 'required',
            'title' => 'required',
            'description' => 'nullable',
            'max_temp' => 'nullable',
            'min_temp' => 'nullable',
            'last_temp' => 'nullable',
            'status' => 'required',
            'started_at' => 'nullable',
            'ended_at' => 'nullable',
            'deployed_at' => 'nullable',
            'last_data_at' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new Devices();
        $data->mix = request('mix');
        $data->section = request('section');
        $data->deveui = request('deveui');
        $data->user = request('user');
        $data->type = request('type');
        $data->title = request('title');
        $data->description = request('description');
        $data->max_temp = request('max_temp');
        $data->min_temp = request('min_temp');
        $data->last_temp = request('last_temp');
        $data->status = request('status');
        $data->started_at = request('started_at');
        $data->ended_at = request('ended_at');
        $data->deployed_at = request('deployed_at');
        $data->last_data_at = Carbon ::now();;
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'devices';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 1;
            $log->info = 'Devices '.$data->id.' Created for the Section '.$data->section;
            $log->save();

            return $this->apiResponse(ResaultType::Success, $data, 'Devices Created', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Devices not saved', 500);
        }
    }

    public function show($id)
    {
        $data = Devices::where('devices.deveui','=',$id)
        ->join('section','section.id','=','devices.section')
        ->join('project','project.id','=','section.project')
        ->join('company','company.id','=','project.company')
        ->join('mix','mix.id','=','devices.mix')
        ->join('users','users.id','=','devices.user')
        ->select('devices.*','section.id as sectionID', 'project.id as projectID', 'company.id as companyID','section.title as sectionTitle', 'project.title as projectTitle', 'company.name as companyName', 'mix.title as mixTitle', 'users.name as userName')
        ->first();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Devices Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Devices Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'nullable',
            'title' => 'nullable',
            'type' => 'nullable',
            'description' => 'nullable',
            'mix' => 'nullable',
            'max_temp' => 'nullable',
            'min_temp' => 'nullable',
            'last_temp' => 'nullable',
            'readed_max' => 'nullable',
            'readed_min' => 'nullable',
            'status' => 'nullable',
            'started_at' => 'nullable',
            'ended_at' => 'nullable',
            'deployed_at' => 'nullable',
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = Devices::where('deveui','=',$id)->first();

        if ($data) {
            if (request('type') != '') {
                $data->type = request('type');
            }
            if (request('user') != '') {
                $data->user = request('user');
            }
            if (request('title') != '') {
                $data->title = request('title');
            }
            if (request('description') != '') {
                $data->description = request('description');
            }
            if (request('mix') != '') {
                $data->mix = request('mix');
            }
            if (request('max_temp') != '') {
                $data->max_temp = request('max_temp');
            }
            if (request('min_temp') != '') {
                $data->min_temp = request('min_temp');
            }
            if (request('last_temp') != '') {
                $data->last_temp = request('last_temp');
            }
            if (request('readed_max') != '') {
                $data->readed_max = request('readed_max');
            }
            if (request('readed_min') != '') {
                $data->readed_min = request('readed_min');
            }
            if (request('last_temp') != '') {
                $data->last_temp = request('last_temp');
            }
            $data->status = request('status');
            if (request('started_at') != '') {
                $data->started_at = request('started_at');
            }
            if (request('ended_at') != '') {
                $data->ended_at = request('ended_at');
            }
            if (request('deployed_at') != '') {
                $data->deployed_at = request('deployed_at');
            }
            $data->save();

            if ($data) {
                $log = new Log();
                $log->area = 'devices';
                $log->areaid = $data->id;
                $log->user = Auth::id();
                $log->ip = \Request::ip();
                $log->type = 2;
                $log->info = 'Devices '.$data->id.' Updated in Section '.$data->section;
                $log->save();
                return $this->apiResponse(ResaultType::Success, $data, 'Devices Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Devices not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Warning, null, 'Data not found', 404);
        }
    }

    public function destroy($id)
    {
        $data = Devices::find($id);
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Devices Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

