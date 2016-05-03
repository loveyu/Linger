<?php
/**
 * User: loveyu
 * Date: 2016/5/1
 * Time: 17:12
 */

namespace UView;


use Core\Page;
use ULib\Picture;

/**
 * Class DataApi
 * 数据查询接口
 * @package UView
 */
class DataApi extends Page
{
    /**
     * DataApi constructor.
     * 构造器
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $num
     * @param int $page
     */
    public function get_new_pics($num = 15, $page = 1)
    {
        $this->__lib('Picture');
        $num = intval($num);
        $page = intval($page);
        $num = ($num >= 5 && $num < 50) ? $num : 15;
        $page = ($page >= 1 && $page <= 100) ? $page : 1;
        $pic = new Picture();
        $data = $pic->select_new_pic($num, ($page - 1) * $num);
        send_json_header();
        echo json_encode([
            'data'   => $data,
            'status' => true
        ]);
    }
}