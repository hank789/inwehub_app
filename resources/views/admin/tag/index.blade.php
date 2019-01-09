@extends('admin/public/layout')
@section('title')标签管理@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            标签管理
            <small>管理系统的所有标题(tag)</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.tag.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加标签"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.tag.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.tag.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" id="category_id" name="category_id" value="" />

                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="id" placeholder="id" value="{{ $filter['id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-3">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select id="select_tags" name="select_tags" class="form-control" >
                                                @if ($filter['category_id'] == -1)
                                                    <option value="-1" selected>不选择</option>
                                                @endif
                                                @include('admin.category.option',['type'=>'all','select_id'=>$filter['category_id'],'root'=>false])
                                            </select>
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
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>ID</th>
                                        <th>图标</th>
                                        <th>名称</th>
                                        <th>分类</th>
                                        <th>简介</th>
                                        <th>关联数</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($tags as $tag)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $tag->id }}"/></td>
                                            <td>{{ $tag->id }}</td>
                                            <td> @if($tag->logo)
                                                    <img src="{{ $tag->logo }}"  style="width: 27px;"/>
                                                @endif
                                            </td>
                                            <td><a href="{{ route('ask.tag.index',['id'=>$tag->id]) }}" target="_blank">{{ $tag->name }}</a></td>
                                            <td>{{ implode(',',$tag->categories->pluck('name')->toArray()) }}</td>
                                            <td width="30%">{{ $tag->summary }}</td>
                                            <td>{{ $tag->countMorph() }}</td>
                                            <td>{{ timestamp_format($tag->created_at) }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.tag.edit',['id'=>$tag->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
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
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.tag.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $tags->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $tags->appends($filter)->render()) !!}
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
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_tags',"{{ route('admin.tag.index') }}");
        $("#select_tags").select2({
            theme:'bootstrap',
            placeholder: "分类",
            minimumInputLength:2,
            tags:false
        });

        $("#select_tags").change(function(){
            $("#category_id").val($("#select_tags").val());
        });
    </script>
@endsection