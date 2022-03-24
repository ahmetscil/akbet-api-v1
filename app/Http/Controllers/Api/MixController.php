<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Mix;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Validator;

class MixController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $query = Mix::query();

        if ($request->has('company')) {
            $query->where('mix.company', '=', request('company'));
        }
        if ($request->has('status'))
            $query->where('mix.status', '=', $request->query('status'));

        $query->join('company','company.id','=','mix.company');
        $query->join('users','users.id','=','mix.user');
        $query->select('mix.*','company.name as companyName', 'users.name as userName');

        $length = count($query->get());
        $data = $query->offset($offset)->limit($limit)->get();

        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Mix Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'user' => 'required',
            'status' => 'nullable',
            'title' => 'required',
            'activation_energy' => 'nullable',
            'temperature' => 'nullable',
            'a' => 'nullable',
            'b' => 'nullable'
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new Mix();
        $data->company = request('company');
        $data->user = request('user');
        $data->status = request('status');
        $data->title = request('title');
        $data->activation_energy = request('activation_energy');
        $data->temperature = request('temperature');
        $data->a = request('a');
        $data->b = request('b');
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'mix';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 1;
            $log->info = 'Mix '.$data->id.' Created for the Company '.$data->company;
            $log->save();
            return $this->apiResponse(ResaultType::Success, $data, 'Mix Created', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Mix not saved', 500);
        }
    }

    public function show($id)
    {
        $data = Mix::find($id);
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Mix Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Mix Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable',
            'title' => 'nullable',
            'activation_energy' => 'nullable',
            'temperature' => 'nullable',
            'a' => 'nullable',
            'b' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = Mix::find($id);

        if ($data) {
            $data->status = request('status');
            if (request('title') != '') {
                $data->title = request('title');
            }
            if (request('activation_energy') != '') {
                $data->activation_energy = request('activation_energy');
            }
            if (request('temperature') != '') {
                $data->temperature = request('temperature');
            }
            if (request('a') != '') {
                $data->a = request('a');
            }
            if (request('b') != '') {
                $data->b = request('b');
            }
            $data->save();

            if ($data) {
                $log = new Log();
                $log->area = 'mix';
                $log->areaid = $data->id;
                $log->user = Auth::id();
                $log->ip = \Request::ip();
                $log->type = 2;
                $log->info = 'Mix '.$data->id.' Updated in Company '.$data->company;
                $log->save();
                return $this->apiResponse(ResaultType::Success, $data, 'Mix Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Mix not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Warning, null, 'Data not found', 404);
        }
    }

    public function destroy($token)
    {
        $data = Mix::where('token','=',$token)->first();
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Mix Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

