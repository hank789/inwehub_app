@extends('admin.public.layout')
@section('title')小程序定制化客户Oauth管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            小程序定制化客户认证管理
            <small>管理客户Oauth认证</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-2">
                                <div class="btn-group">
                                    <a href="{{ route('admin.partner.oauth.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新客户"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.partner.oauth.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.partner.oauth.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.partner.oauth.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="app_id" placeholder="客户标示" value="{{ $filter['app_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_app_version_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-1">
                                            <button type="submit" class="btn btn-primary">搜索</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body  no-padding">
                        <form name="itemForm" id="item_form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>客户标示</th>
                                        <th>密匙</th>
                                        <th>产品</th>
                                        <th>描述</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($oauthList as $oauth)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $oauth->id }}"/></td>
                                            <td>{{ $oauth->app_id }}</td>
                                            <td>{{ $oauth->app_secret }}</td>
                                            <td>{{ $oauth->product->name }}</td>
                                            <td>{{ $oauth->description }}</td>
                                            <td><span class="label @if($oauth->status===0) label-danger  @else label-success @endif">{{ trans_app_version_status($oauth->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.partner.oauth.edit',['id'=>$oauth->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.partner.oauth.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="创建新客户"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.partner.oauth.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="禁用选中项" onclick="confirm_submit('item_form','{{  route('admin.partner.oauth.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $oauthList->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $oauthList->appends($filter)->render()) !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_partner',"{{ route('admin.partner.oauth.index') }}");
    </script>
@endsection