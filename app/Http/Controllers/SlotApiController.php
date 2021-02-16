<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SlotApiController extends Controller
{

    public function __construct()
    {
        
    }

    public function index($reqest)
    {

        $endpoint = "http://slot99n.com/createplayer.html";
        $client = new Client();

        $response = $client->request('GET', $endpoint, ['query' => [
            'adminid' => 'Steve',
            'playerid' => 'test1',
            'playername' => '테스트1계정',
            'bankcd' => '테스트은행',
            'banknum' => '111-1111-1111',
            'bankname' => '예금주',
        ]]);

        $statusCode = $response->getStatusCode();
        $content = $response->getBody();

        dd($content);
    }
}
