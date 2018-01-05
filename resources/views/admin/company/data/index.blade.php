@extends('admin/public/layout')

@section('title')企业信息@endsection

@section('content')
    <section class="content-header">
        <h1>企业信息</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.company.data.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加企业"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.company.data.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过选中项" onclick="confirm_submit('item_form','{{  route('admin.company.data.unverify') }}','确认审核不通过选中项？')"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除企业" onclick="confirm_submit('item_form','{{  route('admin.company.data.destroy',['id'=>0]) }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.company.data.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-4">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
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
                                        <th>LOGO</th>
                                        <th>地址</th>
                                        <th>geohash</th>
                                        <th>人员数</th>
                                        <th>标签</th>
                                        <th>审核状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($companies as $item)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $item->id }}" name="ids[]"/></td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>
                                                <img width="100" height="100" src="{{ $item->logo }}">
                                            </td>
                                            <td>{{ $item->address_province }}</td>
                                            <td>{{ $item->geohash }}</td>
                                            <td>{{ $item->getPeopleNumber() }}</td>
                                            <td>{{ implode(',',$item->tags()->pluck('name')->toArray()) }}</td>
                                            <td><span class="label @if($item->audit_status===0) label-warning @elseif($item->audit_status===1) label-success  @else label-danger @endif">{{ trans_authentication_status($item->audit_status) }}</span> </td>
                                            <td>{{ $item->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.company.data.edit',['id'=>$item->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.company.data.createPeople',['data_id'=>$item->id]) }}" data-toggle="tooltip" title="添加相关人员"><i class="fa fa-user-plus"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.company.data.people',['data_id'=>$item->id]) }}" data-toggle="tooltip" title="相关人员"><i class="fa fa-user-md"></i></a>
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
        set_active_menu('manage_company',"{{ route('admin.company.data.index') }}");
    </script>
@endsection