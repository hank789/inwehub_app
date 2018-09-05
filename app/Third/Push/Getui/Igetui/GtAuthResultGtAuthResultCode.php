<?php namespace App\Third\Push\Getui\Igetui;


/**
 * @author: wanghui
 * @date: 2017/5/10 下午4:28
 * @email: hank.huiwang@gmail.com
 */

class GtAuthResultGtAuthResultCode extends PBEnum
{
    const successed  = 0;
    const failed_noSign  = 1;
    const failed_noAppkey  = 2;
    const failed_noTimestamp  = 3;
    const failed_AuthIllegal  = 4;
    const redirect  = 5;
}
