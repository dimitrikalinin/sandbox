<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Exceptions\JsonApiException;


class SmarthomeController extends Controller
{
    public function index(Request $request): Response
    {
        Log::debug('headers', [$request->header()]);
        
        $response = '{
            "links": {
              "self": "' . route('smarthomes.get') . '"
            },
            "data": [],
            "meta": {
              "total": 0
            }
          }';
        
        return response($response, 200, ['Content-Type' => 'application/vnd.api+json']);    
    }
    
    public function upsert(Request $request, $id=null): Response
    {
        //throw new JsonApiException('no implementation');
        
        $response = '';
        $code = 204;
        if (!empty($id)) {
            $response = '{
                "data": {
                  "type": "smarthomes",
                  "id": "' . $id . '",
                  "attributes": {
                    "description": "Ember Hamster",
                  },
                  "links": {
                    "self": "' . route('smarthomes.get', ['id' => $id]) . '"
                  }
                }
              }';
            $code = 201;
        }
        
        return response($response, $code, ['Content-Type' => 'application/vnd.api+json']);
    }
}
