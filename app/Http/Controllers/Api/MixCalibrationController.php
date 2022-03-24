<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\MixCalibration;
use App\Mix;


use Illuminate\Http\Request;
use Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class MixCalibrationController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $type = $request->type ? $request->type : null;
        $query = MixCalibration::query();
        $length = 1;
        if ($request->has('type')){
            $query->where('type', $request->query('type'));
        } else {
            $length = count($query->get());
        }
        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('mix')) {
            $query->where('mix', $request->query('mix'));
            $mix = Mix::where('mix.id','=',$request->query('mix'))->first();
            $query->where('mix_calibration.mix', $mix->id);
        }

        $query->join('mix','mix.id','=','mix_calibration.mix');
        $query->select('mix_calibration.*','mix.title as mixTitle');
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
            'mix' => 'required',
            'days' => 'required',
            'strength' => 'required',
            'status' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new MixCalibration();
        $data->mix = request('mix');
        $data->days = request('days');
        $data->strength = request('strength');
        $data->status = request('status');
        $data->save();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'MixCalibration Added', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'MixCalibration not Added', 500);
        }
    }

    public function show($id)
    {
        $data = MixCalibration::find($id);
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'Section Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable',
            'strength' => 'nullable',
            'status' => 'nullable',
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = MixCalibration::find($id);
        if (request('mix')) {
            $data->mix = request('mix');
        }
        if (request('days')) {
            $data->days = request('days');
        }
        if (request('strength')) {
            $data->strength = request('strength');
        }
        $data->status = request('status');
        $data->save();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'MixCalibration Updated', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content not updated', 500);
        }
    }

    public function destroy($id)
    {
        $data = MixCalibration::find($id);
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'MixCalibration Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

