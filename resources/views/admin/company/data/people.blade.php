@extends('admin/public/layout')

@section('title')企业相关人员@endsection

@section('content')
    <section class="content-header">
        <h1>企业相关人员</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.company.data.createPeople') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加企业人员"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.company.data.verifyPeople') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过选中项" onclick="confirm_submit('item_form','{{  route('admin.company.data.unverifyPeople') }}','确认审核不通过选中项？')"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除" onclick="confirm_submit('item_form','{{  route('admin.company.data.destroyPeople',['id'=>0]) }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.company.data.people') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="data_id" placeholder="公司id" value="{{ $filter['data_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="用户id" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-3">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_authentication_status('all') as $key => $status)
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
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>ID</th>
                                        <th>公司名</th>
                                        <th>公司Logo</th>
                                        <th>用户</th>
                                        <th>状态</th>
                                        <th>审核状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($companies as $item)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $item->id }}" name="ids[]"/></td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->companyData->name }}</td>
                                            <td>
                                                <img width="100" height="100" src="{{ $item->companyData->logo }}">
                                            </td>
                                            <td>{{ $item->user->name.'['.$item->user_id.']' }}</td>
                                            <td>{{ $item->statusInfo() }}</td>
                                            <td><span class="label @if($item->audit_status===0) label-warning @elseif($item->audit_status===1) label-success  @else label-danger @endif">{{ trans_authentication_status($item->audit_status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.company.data.editPeople',['id'=>$item->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        {!! str_replace('/?', '?', $companies->appends($filter)->render()) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_company',"{{ route('admin.company.data.people') }}");
    </script>
@endsection