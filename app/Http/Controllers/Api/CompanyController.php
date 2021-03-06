<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Company;
use App\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Validator;

class CompanyController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $query = Company::query();

        if ($request->has('search'))
            $query->where('title', 'like', '%' . $request->query('search') . '%');

        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects);
        }
        $length = count($query->get());
        $query->whereNotIn('status', [9]);

        $data = $query->offset($offset)->limit($limit)->get();

        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main' => 'nullable',
            'name' => 'required',
            'email' => 'required',
            'tel' => 'nullable',
            'city' => 'nullable',
            'country' => 'nullable',
            'address' => 'nullable',
            'level' => 'nullable',
            'logo' => 'nullable',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new Company();
        $data->main = request('main');
        $data->name = request('name');
        $data->email = request('email');
        $data->tel = request('tel');
        $data->city = request('city');
        $data->country = request('country');
        $data->address = request('address');
        $data->level = request('level');
        $data->logo = request('logo');
        $data->status = request('status');
        $data->save();
        if ($data) {
            $log = new Log();
            $log->area = 'company';
            $log->areaid = $data->id;
            $log->user = Auth::id();
            $log->ip = \Request::ip();
            $log->type = 1; // create
            $log->info = 'Company Created';
            $log->save();

            return $this->apiResponse(ResaultType::Success, $data, 'Company Created', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content not saved', 500);
        }
    }

    public function show($id)
    {
        $data = Company::where('id','=',$id)->first();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Content Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'main' => 'nullable',
            'name' => 'nullable',
            'email' => 'nullable',
            'tel' => 'nullable',
            'city' => 'nullable',
            'country' => 'nullable',
            'address' => 'nullable',
            'level' => 'nullable',
            'logo' => 'nullable',
            'status' => 'nullable',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = Company::where('id','=',$id)->first();

        if ($data) {
            if (request('main') != '') {
                $data->main = request('main');
            }
            if (request('name') != '') {
                $data->name = request('name');
            }
            if (request('email') != '') {
                $data->email = request('email');
            }
            if (request('tel') != '') {
                $data->tel = request('tel');
            }
            if (request('city') != '') {
                $data->city = request('city');
            }
            if (request('country') != '') {
                $data->country = request('country');
            }
            if (request('address') != '') {
                $data->address = request('address');
            }
            if (request('level') != '') {
                $data->level = request('level');
            }
            if (request('logo') != '') {
                $data->logo = request('logo');
            }
                $data->status = request('status');
            $data->save();

            if ($data) {
                $log = new Log();
                $log->area = 'company';
                $log->areaid = $data->id;
                $log->user = Auth::id();
                $log->ip = \Request::ip();
                $log->type = 2; // update
                $log->info = 'Company Updated';
                $log->save();

                return $this->apiResponse(ResaultType::Success, $data, 'Company Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Company not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Warning, null, 'Data not found', 404);
        }
    }

    public function destroy($id)
    {
        $data = Company::find($id);
        if (count($data) >= 1) {
            $data->status = 9;
            $data->save();
            // $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Company Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

