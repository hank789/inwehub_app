@extends('emails.layout')

@section('title')微信公众号扫码认证@endsection

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="content-block">
                二维码地址：{{ $url }}
                <br />
                如非本人操作，请忽略此邮件！
            </td>
        </tr>
        <tr>
            <td class="content-block">
                &mdash; {{ Setting()->get('website_name') }}
            </td>
        </tr>
    </table>
@endsection