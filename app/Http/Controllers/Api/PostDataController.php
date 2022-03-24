<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\PostData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;

class PostDataController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $query = PostData::query();

        if ($request->has('search'))
            $query->where('title', 'like', '%' . $request->query('search') . '%');

        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('sort', 'DESC'));

        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects);
        } else {
            $query->select('devices.*','section.title as sectionTitle');
        }

        $length = count($query->get());
        $data = $query->offset($offset)->limit($limit)->get();
        $query->join('section', 'section.id', '=', 'devices.section');
        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'PostData Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area' => 'required',
            'areaid' => 'required',
            'temptature' => 'required',
            'maturity' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = new PostData();
        $data->area = request('area');
        $data->areaid = request('areaid');
        $data->temptature = request('temptature');
        $data->maturity = request('maturity');
        $data->save();
        if ($data) {
            return $this->apiResponse(ResaultType::Success, $data, 'PostData Created', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'PostData not saved', 500);
        }
    }

    public function show($id)
    {
        $data = PostData::find($id);
        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'PostData Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'PostData Not Found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'temptature' => 'nullable',
            'maturity' => 'nullable',
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $data = PostData::find($id);

        if (count($data) >= 1) {
            if (request('temptature') != '') {
                $data->temptature = request('temptature');
            }
            if (request('maturity') != '') {
                $data->maturity = request('maturity');
            }
            $data->save();

            if ($data) {
                return $this->apiResponse(ResaultType::Success, $data, 'PostData Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'PostData not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Warning, null, 'Data not found', 404);
        }
    }

    public function destroy($id)
    {
        $data = PostData::find($id);
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'PostData Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

