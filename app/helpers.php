<?php
if (! function_exists('markdown')) {
    /**
     * Compile Markdown to HTML.
     *
     * @param string|null $text
     * @return string
     */
    function markdown($text = null) {
        return app(ParsedownExtra::class)->text($text);
    }
}
if (! function_exists('gravatar_profile_url')) {
    /**
     * Generate gravatar profile page url.
     *
     * @param  string $email
     * @return string
     */
    function gravatar_profile_url($email)
    {
        return sprintf("//www.gravatar.com/%s", md5($email));
    }
}
if (! function_exists('gravatar_url')) {
    /**
     * Generate gravatar image url.
     *
     * @param  string  $email
     * @param  integer $size
     * @return string
     */
    function gravatar_url($email, $size = 48)
    {
        return sprintf("//www.gravatar.com/avatar/%s?s=%s", md5($email), $size);
    }
}
if (! function_exists('attachments_path')) {
    /**
     * Generate attachments path.
     *
     * @param string $path
     * @return string
     */
    function attachments_path($path = null)
    {
        return public_path('files'.($path ? DIRECTORY_SEPARATOR.$path : $path));
    }
}
if (! function_exists('format_filesize')) {
    /**
     * Calculate human-readable file size string.
     *
     * @param $bytes
     * @return string
     */
    function format_filesize($bytes)
    {
        if (! is_numeric($bytes)) return 'NaN';
        $decr = 1024;
        $step = 0;
        $suffix = ['bytes', 'KB', 'MB'];
        while (($bytes / $decr) > 0.9) {
            $bytes = $bytes / $decr;
            $step ++;
        }
        return round($bytes, 2) . $suffix[$step];
    }
}
if (! function_exists('link_for_sort')) {
    /**
     * Build HTML anchor tag for sorting
     *
     * @param string $column
     * @param string $text
     * @param array  $params
     * @return string
     */
    function link_for_sort($column, $text, $params = [])
    {
        $direction = request()->input('order');
        $reverse = ($direction == 'asc') ? 'desc' : 'asc';
        if (request()->input('sort') == $column) {
            // Update passed $text var, only if it is active sort
            $text = sprintf(
                "%s %s",
                $direction == 'asc'
                    ? '<i class="fa fa-sort-alpha-asc"></i>'
                    : '<i class="fa fa-sort-alpha-desc"></i>',
                $text
            );
        }
        $queryString = http_build_query(array_merge(
            request()->except(['sort', 'order']),
            ['sort' => $column, 'order' => $reverse],
            $params
        ));
        return sprintf(
            '<a href="%s?%s">%s</a>',
            urldecode(request()->url()),
            $queryString,
            $text
        );
    }
}
if (! function_exists('cache_key')) {
    /**
     * Generate key for caching.
     *
     * Note that, even though the request endpoints are the same
     *     the response body may be different because of the query string.
     *
     * @param $base
     * @return string
     */
    function cache_key($base)
    {
        $key = ($query = request()->getQueryString())
            ? $base . '.' . urlencode($query)
            : $base;
        return md5($key);
    }
}
if (! function_exists('taggable')) {
    /**
     * Determine if the current cache driver has cacheTags() method
     *
     * @return bool
     */
    function taggable()
    {
        return in_array(config('cache.default'), ['memcached', 'redis'], true);
    }
}
if (! function_exists('current_url')) {
    /**
     * Build current url string, without return param.
     *
     * @return string
     */
    function current_url()
    {
        if (! request()->has('return')) {
            return request()->fullUrl();
        }
        return sprintf(
            '%s?%s',
            request()->url(),
            http_build_query(request()->except('return'))
        );
    }
}
if (! function_exists('array_transpose')) {
    /**
     * Transpose the given array.
     *
     * @param array $data
     * @return array
     */
    function array_transpose(array $data)
    {
        $res = [];
        foreach ($data as $row => $columns) {
            foreach ($columns as $row2 => $column2) {
                $res[$row2][$row] = $column2;
            }
        }
        return $res;
    }
}
if (! function_exists('is_api_domain')) {
    /**
     * Determine if the current request is for HTTP api.
     *
     * @return bool
     */
    function is_api_domain()
    {
        return starts_with(request()->getHttpHost(), config('project.api_domain'));
    }
}
if (! function_exists('jwt')) {
    /**
     * Create JWT instance.
     *
     * @return \Tymon\JWTAuth\JWTAuth
     */
    function jwt() {
        return app('tymon.jwt.auth');
    }
}
if (! function_exists('optimus')) {
    /**
     * Create Optimus instance.
     *
     * @param int|null $id
     * @return int|\Jenssegers\Optimus\Optimus
     */
    function optimus($id = null)
    {
        $factory = app('optimus');
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->encode($id);
    }
}


if (! function_exists('tocview')){
    function tocview($amount){
        $floatLen   = 8;
        $strLen     = strlen($amount);

        if ($strLen < $floatLen){
            $loopnum = $floatLen - $strLen;
            $leftNum = '';
            for ($i = 0; $i < $loopnum+1;$i++){
                if ($i === 0){
                    $leftNum = '0.';
                } else {
                    $leftNum .= '0';
                }
            }
            $reamount = $leftNum. ''. $amount;
        }

        if ($strLen == $floatLen ){
            $reamount = '0.' . $amount;
        }

        if ($strLen > $floatLen){
            $leftLen = $strLen - $floatLen;
            $amountArr = str_split($amount);
            $reString = '';
            for ($i = 0; $i < $strLen;$i++){
                if ($i == $leftLen){
                    $reString .= '.'. $amountArr[$i];
                } else {
                    $reString .= $amountArr[$i];
                }
            }
            $reamount = $reString;
        }



        return $reamount;
    }
}

if (! function_exists('chageDemit')) {
    function chageDemit($Have_Money)
    {
        $deciaml_len = 9;
        $loop_cnt = 0;
        $revert = '';
        $str_len = strlen("$Have_Money");

        for ($i = $str_len; $i >= 0; $i--) {

            $this_str = substr("$Have_Money", $i, 1);

            if ($loop_cnt === $deciaml_len) {
                $revert = $this_str . '.' . $revert;
            } else {
                $revert = $this_str . '' . $revert;
            }
            $loop_cnt++;
        }
        return number_format($revert, 2);
    }
}

if (! function_exists('chageNumber')) {
    function chageNumber($Have_Money)
    {
        $deciaml_len = 9;
        $loop_cnt = 0;
        $revert = '';
        $str_len = strlen("$Have_Money");

        for ($i = $str_len; $i >= 0; $i--) {

            $this_str = substr("$Have_Money", $i, 1);

            if ($loop_cnt === $deciaml_len) {
                $revert = $this_str . '.' . $revert;
            } else {
                $revert = $this_str . '' . $revert;
            }
            $loop_cnt++;
        }
        return number_format($revert);
    }
}


if (! function_exists('changeTimeArea')) {
    function changeTimeArea($time)
    {
        $time = \Illuminate\Support\Carbon::parse($time)->timestamp;
        return \Illuminate\Support\Carbon::parse($time)->timezone('Asia/Ulaanbaatar')->toDateTimeString();
    }
}


if(! function_exists('rightAboveRecommend')){
    function rightAboveRecommend($id)
    {
        //바로 위 회원 을 조회
        $recommend = \App\Recommend::where('user_id', $id)->first();
        $rev_step = '';
        if ((int)$recommend->step1_id === (int)$id){
            $rev_step = 1;
        } elseif ((int)$recommend->step2_id === (int)$id){
            $rev_step = 2;
        } elseif ((int)$recommend->step3_id === (int)$id){
            $rev_step = 3;
        } elseif ((int)$recommend->step4_id === (int)$id){
            $rev_step = 4;
        }
        $step = 'step'. $rev_step . '_id';
        return \App\User::find($recommend->$step);
    }
}

if(! function_exists('rightAboveRecommendEdit')){
    function rightAboveRecommendEdit($id)
    {
        //바로 위 회원 을 조회
        $recommend = \App\Recommend::where('user_id', $id)->first();
        $rev_step = '';
        if ((int)$recommend->step2_id === (int)$id){
            $rev_step = 1;
        } elseif ((int)$recommend->step3_id === (int)$id){
            $rev_step = 2;
        } elseif ((int)$recommend->step4_id === (int)$id){
            $rev_step = 3;
        } elseif ((int)$recommend->step5_id === (int)$id){
            $rev_step = 4;
        }
        $step = 'step'. $rev_step . '_id';
        return \App\User::find($recommend->$step);
    }
}

if(! function_exists('sendTelegramMsgDepositEgg')){
    function sendTelegramMsgDepositEgg($id, $amount)
    {
       /* $users      = \App\User::where('id', $id)->first();
        //$telegrams  = \App\User::whereNotNull('telegram_id')->get();
        $telegrams = array('1204462147', '1676503264');

        foreach ($telegrams as $telegram)
        {
            $arr = [
                'deposit_amount' => $amount,
                'admin_telegram_id' => $telegram,
            ];

            $users->notify(new \App\Notifications\AdminDepositEgg($arr));
        }*/
    }
}


if(! function_exists('sendTelegramMsgDeposit')){
    function sendTelegramMsgDeposit($id, $amount)
    {
        $users      = \App\User::where('id', $id)->first();
        //$telegrams  = \App\User::whereNotNull('telegram_id')->get();
        $telegrams = array('1204462147', '1676503264');

        foreach ($telegrams as $telegram)
        {
            $arr = [
                'deposit_amount' => $amount,
                'admin_telegram_id' => $telegram,
            ];

            $users->notify(new \App\Notifications\Deposit($arr));
        }

    }
}

if(! function_exists('sendTelegramMsgWithDrawl')){
    function sendTelegramMsgWithDrawl($id, $amount)
    {
        $users      = \App\User::where('id', $id)->first();
        //$telegrams  = \App\User::whereNotNull('telegram_id')->get();
        $telegrams = array('1204462147', '1676503264');

        foreach ($telegrams as $telegram)
        {
            $arr = [
                'refund_amount' => $amount,
                'admin_telegram_id' => $telegram,
            ];

            $users->notify(new \App\Notifications\Withdrawal($arr));
        }


    }
}


if(! function_exists('sendTelegramMsgAdminRefund')){
    function sendTelegramMsgAdminRefund($id, $amount)
    {
        /*$users      = \App\User::where('id', $id)->first();
         //$telegrams  = \App\User::whereNotNull('telegram_id')->get();
         $telegrams = array('1204462147', '1676503264');

         foreach ($telegrams as $telegram)
         {
             $arr = [
                 'refund_amount' => $amount,
                 'admin_telegram_id' => $telegram,
             ];

             $users->notify(new \App\Notifications\AdminRefund($arr));
         }*/
    }
}

