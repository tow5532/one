<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyInfoLosing extends Model
{
    protected $table = 'daily_info_losing';
    /**
     * @var mixed|string
     */
    private $search_date;
    /**
     * @var mixed|string
     */
    private $user_id;
    /**
     * @var mixed|string
     */
    private $username;
    /**
     * @var mixed|string
     */
    private $total_deposit;
    /**
     * @var mixed|string
     */
    private $total_refund;
    /**
     * @var mixed|string
     */
    private $total_point;
    /**
     * @var mixed|string
     */
    private $term_point;
    /**
     * @var mixed|string
     */
    private $past_user_point;
    /**
     * @var mixed|string
     */
    private $user_losing;
    /**
     * @var mixed|string
     */
    private $store_id;
    /**
     * @var mixed|string
     */
    private $store_commission;
}
