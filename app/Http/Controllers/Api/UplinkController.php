<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Uplink;
use App\Devices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;

class UplinkController extends ApiController
{
    public function index(Request $request)
    {
        $offset = $request->offset ? $request->offset : 0;
        $limit = $request->limit ? $request->limit : 50;
        $query = Uplink::query();
        if ($request->has('search'))
            $query->where('DevEUI', '=', $request->query('search'));
        if ($request->has('select')) {
            $selects = explode(',', $request->query('select'));
            $query->select($selects)->orderBy('id','DESC');
        } else {
            $query->select('uplink.*')->orderBy('id','DESC');
        }

        $length = count($query->get());
        $data = $query->offset($offset)->limit($limit)->get();
        if (count($data) >= 1) {
            return $this->apiResponse(ResaultType::Success, $data, 'Listing: ' . $length, 200);
        } else {
            return $this->apiResponse(ResaultType::Error, null, 'Devices Not Found', 0, 404);
        }
    }

    public function store(Request $request)
    {



        function hex_to_string($hex) {
            if (strlen($hex) % 2 != 0) {
                throw new Exception('String length must be an even number.', 1);
            }
            $string = '';
            for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
                $string .= chr(hexdec($hex[$i].$hex[$i+1]));
            }
            return $string;
        }

        $datas = $request->json()->get('DevEUI_uplink');;
        $DevEUI = $datas['DevEUI'];
        $device = Devices::where('deveui','=',$DevEUI)->first();
        if ($device) {

            $uplinkHex = $datas['payload_hex'];
            $splitQuery = explode(' ', hex_to_string($uplinkHex));
            $temp = explode('=',$splitQuery[0]);
            $deviceTemp = $temp[1]; // burası çok önemli!!
            $data = new Uplink();
            $data->DevEUI = $datas['DevEUI'];
            $data->payload_hex = $datas['payload_hex'];
            $data->LrrRSSI = $datas['LrrRSSI'];
            $data->LrrSNR = $datas['LrrSNR'];
            $data->temperature = $deviceTemp;
            $data->maturity = rand(10,100);
            $data->save();
            if ($data) {
                if($device->readed_max >= $deviceTemp) {
                    $device->readed_max = $deviceTemp;
                }
                if($device->readed_min <= $deviceTemp) {
                    $device->readed_min = $deviceTemp;
                }
                $device->last_data_at = $data->created_at;
                $device->save();
                return $this->apiResponse(ResaultType::Success, $data, 'Uplink Created', 200);
            } else {
                return $this->apiResponse(ResaultType::Error, null, 'Uplink not saved', 500);
            }
        }
    }

    public function show($deveui,$select)
    {
        $query = Uplink::query();
        $query->where('DevEUI', '=', $deveui)->select($select)->orderBy('id','DESC')->limit(50);

        $values = array();

        $data = $query->get();
        foreach ($data as $keys)
        {
            $values[] = $keys->$select;
        };
        return $values;

    }

}

