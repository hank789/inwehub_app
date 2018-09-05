<?php namespace App\Third\Push\Getui\Igetui;
/**
 * @author: wanghui
 * @date: 2017/5/10 下午4:30
 * @email: hank.huiwang@gmail.com
 */


class PushListResult extends PBMessage
{
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader=null)
    {
        parent::__construct($reader);
        $this->fields["1"] = "PushResult";
        $this->values["1"] = array();
    }
    function results($offset)
    {
        return $this->_get_arr_value("1", $offset);
    }
    function add_results()
    {
        return $this->_add_arr_value("1");
    }
    function set_results($index, $value)
    {
        $this->_set_arr_value("1", $index, $value);
    }
    function remove_last_results()
    {
        $this->_remove_last_arr_value("1");
    }
    function results_size()
    {
        return $this->_get_arr_size("1");
    }
}
