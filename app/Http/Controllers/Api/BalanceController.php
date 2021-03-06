<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Balance;
use App\User;
use App\Project;
use Illuminate\Http\Request;
use Validator;

class BalanceController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 99999999999999;
        $token = $request->token ? $request->token : null;
        $query = Balance::query();
        if ($request->has('token')){
            $project = Project::where('token','=',$token)->first();
            $length = count($query->where('project', '=', $project->id)->get());
            $query->where('project', '=', $project->id);
        } else {
            $length = count($query->get());
        }
        if ($request->has('sortBy'))
            $query->orderBy($request->query('sortBy'), $request->query('id', 'DESC'));
            $query->join('project', 'project.id', '=', 'balance.project');
        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects,'project.title as projectTitle', 'project.code as projectCode', 'project.token');
        } else {
            $query->select('balance.*','project.title as projectTitle', 'project.code as projectCode', 'project.token');
        }

        if ($request->has('start')) {
            $start = $request->query('start');
            $end = $request->query('end');
            $query->whereBetween('created_at',[$start,$end]);
        }

        $data = $query->offset($offset)->limit($limit)->get();
        $data->each->setAppends(['bankName']);

        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: '.$offset.'-'.$limit, $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required',
            'project' => 'required|integer',
            'recharge' => 'nullable|string',
            'type' => 'required|integer',
            'bank' => 'nullable|integer',
            'remark' => 'nullable|string',
            'comment' => 'nullable|string',
            'arrival_date' => 'nullable|date',
            'status' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $user = User::where('api_token','=',request('user'))->first();
        if ((count($user) >= 1) && ($user->level == 1)) {
            $data = new Balance();
            $data->project = request('project');
            $data->recharge = request('recharge');
            $data->type = request('type');
            $data->bank = request('bank');
            $data->remark = request('remark');
            $data->arrival_date = request('arrival_date');
            $data->status = request('status');
            $data->save();
            if ($data) {
                $bsn = Project::find($data->project);
                if ($data->type == 3) {
                    $bsn->credit = $bsn->credit + $data->recharge;
                } else {
                    if ($data->recharge) {
                        $bsn->before = $bsn->balance;
                        $bsn->balance = $bsn->balance + $data->recharge;
                    }
                    if ($data->paid) {
                        // paid de??eri bu g??ne kadar HARCANMI?? paray?? g??sterir.
                    }
                }
                $bsn->save();
                return $this->apiResponse(ResaultType::Success, $data, 'Balance Added', 201);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Balance not Added', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'User not found', 500);
        }
    }

    public function show($id)
    {
        $project = Project::where('token','=',$token)->first();
        if (count($project) >= 1) {
            $data = Balance::where('project','=',$project->id)->get();
            // join bank info
            return $this->apiResponse(ResaultType::Success, $data, 'Content Detail', 201);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Content Not Found', 404);
        }
    }

    public function update(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required',
            'bank' => 'nullable|integer',
            'paid_date' => 'nullable',
            'comment' => 'nullable',
            'status' => 'required'
            ]);
        if ($validator->fails()) {
            return $this->apiResponse(ResaultType::Error, $validator->errors(), 'Validation Error', 422);
        }
        $user = User::where('api_token','=',request('user'))->first();
        if ((count($user) >= 1) && ($user->level == 1)) {
            $data = Balance::find($token);
            if (request('bank')) {
                $data->bank = request('bank');
            }
            $data->paid_date = request('paid_date');
            if(request('status') == 1) {
                $data->paid = $data->recharge;
            }
            if(request('status') == 0) {
                $data->paid = 0;
            }
            $data->comment = request('comment');
            $data->status = request('status');
            $data->save();
            if ($data) {
                return $this->apiResponse(ResaultType::Success, $data, 'kkkContent Updated', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'kkkContent not updated', 500);
            }
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'User not found', 500);
        }
    }

    public function destroy($token)
    {
        $data = Balance::where('token','=',$token)->first();
        if (count($data) >= 1) {
            $data->delete();
            return $this->apiResponse(ResaultType::Success, $data, 'Content Deleted', 200);
        } else {
            return $this->apiResponse(ResaultType::Error, $data, 'Deleted Error', 500);
        }
    }
}

