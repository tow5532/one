<?php

namespace App\Admin\Controllers;

use App\Cointype;
use App\DepositStep;
use App\Recommend;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends AdminController
{
    public function selectusers(Request $request)
    {
        $q = $request->get('q');

        return User::where('username', 'like', "%$q%")->whereNotIn('username', ['admin', 'master', 'sunmaster'])->paginate(null, ['id', 'username as text']);
    }

    public function selectcategory(Request $request)
    {
        $q = $request->get('q');

        return Cointype::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public function stepcategory(Request $request)
    {
        $q = $request->get('q');

        return DepositStep::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }

    public  function selectSubcompany(Request $request)
    {
        $q = $request->get('q');
        $users = Recommend::where('step1_id', '=', $q)
            ->whereNotNull('step2_id')
            ->whereNull('step3_id')
            ->whereNull('step4_id')->get();
        $list = array();
        $list[0]['id'] = '0';
        $list[0]['text'] = '선택해주세요.';
        $i = 1;
        foreach ($users as $user)
        {
            $step2 = User::where('id', $user->step2_id)->first();
            $list[$i]['id'] = $user->step2_id;
            $list[$i]['text'] = $step2->username;
            $i++;
        }
        return $list;
    }

    public  function selectDistributor(Request $request)
    {
        $q = $request->get('q');
        $users = Recommend::where('step2_id', '=', $q)
            ->whereNotNull('step3_id')
            ->whereNull('step4_id')
            ->get();
        $list = array();
        $list[0]['id'] = '0';
        $list[0]['text'] = '선택해주세요.';
        $i = 1;
        foreach ($users as $user)
        {
            $step2 = User::where('id', $user->step3_id)->first();
            $list[$i]['id'] = $user->step3_id;
            $list[$i]['text'] = $step2->username;
            $i++;
        }
        return $list;
    }

    public  function selectStore(Request $request)
    {
        $q = $request->get('q');
        $users = Recommend::where('step3_id', '=', $q)
            ->whereNotNull('step4_id')
            ->whereNull('step5_id')
            ->get();
        $list = array();
        $list[0]['id'] = '0';
        $list[0]['text'] = '선택해주세요.';
        $i = 1;
        foreach ($users as $user)
        {
            $step2 = User::where('id', $user->step4_id)->first();
            $list[$i]['id'] = $user->step4_id;
            $list[$i]['text'] = $step2->username;
            $i++;
        }
        return $list;
    }

    public function selectonlyusers(Request $request)
    {
        $q = $request->get('q');

        return User::where('username', 'like', "%$q%")->whereNotIn('username', ['admin', 'master', 'sunmaster'])
            ->where('admin_yn', 'N')
            ->paginate(null, ['id', 'username as text']);
    }

}
