<?php namespace App\Third\Push\Getui\Igetui;


/**
 * @author: wanghui
 * @date: 2017/5/10 下午4:35
 * @email: hank.huiwang@gmail.com
 */

class PushListMessage extends PBMessage
{
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader=null)
    {
        parent::__construct($reader);
        $this->fields["1"] = "PBString";
        $this->values["1"] = "";
        $this->fields["2"] = "PBString";
        $this->values["2"] = "";
        $this->fields["3"] = "Target";
        $this->values["3"] = array();
    }
    function seqId()
    {
        return $this->_get_value("1");
    }
    function set_seqId($value)
    {
        return $this->_set_value("1", $value);
    }
    function taskId()
    {
        return $this->_get_value("2");
    }
    function set_taskId($value)
    {
        return $this->_set_value("2", $value);
    }
    function targets($offset)
    {
        return $this->_get_arr_value("3", $offset);
    }
    function add_targets()
    {
        return $this->_add_arr_value("3");
    }
    function set_targets($index, $value)
    {
        $this->_set_arr_value("3", $index, $value);
    }
    function remove_last_targets()
    {
        $this->_remove_last_arr_value("3");
    }
    function targets_size()
    {
        return $this->_get_arr_size("3");
    }
}
