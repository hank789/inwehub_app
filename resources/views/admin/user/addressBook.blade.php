@extends('admin/public/layout')

@section('title')
    用户管理
@endsection

@section('content')
    <section class="content-header">
        <h1>
            用户通讯录列表
            <small>显示当前系统用户的通讯录</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.user.addressBook') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="phone" placeholder="手机" value="{{ $filter['phone'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="name" placeholder="姓名" value="{{ $filter['name'] or '' }}"/>
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
                                        <th>系统ID</th>
                                        <th>通讯录ID</th>
                                        <th>通讯录姓名</th>
                                        <th>通讯录手机号</th>
                                        <th>通讯录所有者ID</th>
                                        <th>通讯录所有者姓名</th>
                                        <th>创建时间</th>
                                        <th>原始信息</th>
                                    </tr>
                                    @foreach($addressBooks as $addressBook)
                                        <tr>
                                            <td>{{ $addressBook->id }}</td>
                                            <td>{{ $addressBook->address_book_id }}</td>
                                            <td>{{ $addressBook->display_name }}</td>
                                            <td>{{ $addressBook->phone }}</td>
                                            <td>{{ $addressBook->user_id }}</td>
                                            <td>{{ $addressBook->user->name }}</td>
                                            <td>{{ $addressBook->created_at }}</td>
                                            <td>{{ json_encode($addressBook->detail,JSON_UNESCAPED_UNICODE) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $addressBooks->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $addressBooks->appends($filter)->render()) !!}
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
        set_active_menu('manage_user',"{{ route('admin.user.addressBook') }}");
    </script>
@endsection