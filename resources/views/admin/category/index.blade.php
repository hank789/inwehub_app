@extends('admin/public/layout')

@section('title')分类管理@endsection

@section('content')
    <section class="content-header">
        <h1>分类管理</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">

                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-3">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.category.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加分类"><i class="fa fa-plus"></i></a>
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('itemForm','{{  route('admin.category.destroy',['id'=>0]) }}','删除选中分类会同时删除其子分类，确认继续操作？')"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                                <div class="col-xs-9">
                                    <div class="row">
                                        <form name="searchForm" action="{{ route('admin.category.index') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <div class="col-xs-2 hidden-xs">
                                                <input type="text" class="form-control" name="id" placeholder="id" value="{{ $filter['id'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-4">
                                                <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                            </div>
                                            <div class="col-xs-2">
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
                                        <th>排序</th>
                                        <th>名称</th>
                                        <th>父类id</th>
                                        <th>标示</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $category->id }}" name="ids[]"/></td>
                                            <td>{{ $category->id }}</td>
                                            <td>{{ $category->sort }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td>{{ $category->parent_id }}</td>
                                            <td>{{ $category->slug }}</td>
                                            <td>{{ $category->created_at }}</td>
                                            <td><span class="label @if($category->status===0) label-danger  @else label-success @endif">{{ trans_common_status($category->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-primary" href="{{ route('admin.category.edit',['id'=>$category->id]) }}" data-toggle="tooltip" title="编辑分类信息"><i class="fa fa-edit" aria-hidden="true"></i> 编辑</a>
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
                                        <a href="{{ route('admin.category.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加分类"><i class="fa fa-plus"></i></a>
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('itemForm','{{  route('admin.category.destroy',['id'=>0]) }}','删除选中分类会同时删除其子分类，确认继续操作？')"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="text-right">
                                        <span class="total-num">共 {{ $categories->total() }} 条数据</span>
                                        {!! str_replace('/?', '?', $categories->appends($filter)->render()) !!}
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
        set_active_menu('manage_tags',"{{ route('admin.category.index') }}");
    </script>
@endsection