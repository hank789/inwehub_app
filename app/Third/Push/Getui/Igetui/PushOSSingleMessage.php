<?php namespace App\Third\Push\Getui\Igetui;
/**
 * @author: wanghui
 * @date: 2017/5/10 下午4:32
 * @email: hank.huiwang@gmail.com
 */


class PushOSSingleMessage extends PBMessage
{
    var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
    public function __construct($reader=null)
    {
        parent::__construct($reader);
        $this->fields["1"] = "PBString";
        $this->values["1"] = "";
        $this->fields["2"] = "OSMessage";
        $this->values["2"] = "";
        $this->fields["3"] = "Target";
        $this->values["3"] = "";
    }
    function seqId()
    {
        return $this->_get_value("1");
    }
    function set_seqId($value)
    {
        return $this->_set_value("1", $value);
    }
    function message()
    {
        return $this->_get_value("2");
    }
    function set_message($value)
    {
        return $this->_set_value("2", $value);
    }
    function target()
    {
        return $this->_get_value("3");
    }
    function set_target($value)
    {
        return $this->_set_value("3", $value);
    }
}
