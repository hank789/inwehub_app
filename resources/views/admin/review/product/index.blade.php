@extends('admin/public/layout')
@section('title')产品管理@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            产品管理
            <small>管理点的服务和产品</small>
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
                                    <a href="{{ route('admin.review.product.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加产品"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.review.product.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" id="category_id" name="category_id" value="" />
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="id" placeholder="id" value="{{ $filter['id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <div>
                                                <label><input type="checkbox" name="onlyZh" value="1" @if ( $filter['onlyZh']??0) checked @endif >中文</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <select id="select_tags" name="select_tags" class="form-control" >
                                                @if ($filter['category_id'] == -1)
                                                    <option value="-1" selected>不选择</option>
                                                @endif
                                                @include('admin.category.option',['type'=>['product_album','enterprise_review'],'select_id'=>$filter['category_id'],'root'=>false, 'last'=>true])
                                            </select>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="order_by">
                                                <option value="0">排序</option>
                                                <option value="reviews|asc" @if( isset($filter['order_by']) && $filter['order_by']=='reviews|asc') selected @endif >点评升序</option>
                                                <option value="reviews|desc" @if( isset($filter['order_by']) && $filter['order_by']=='reviews|desc') selected @endif >点评降序</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_authentication_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
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
                                        <th>ID</th>
                                        <th>图标</th>
                                        <th>名称</th>
                                        <th>分类</th>
                                        <th>点评数</th>
                                        <th>简介</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($tags as $tag)
                                        <tr id="submission_{{ $tag->id }}" class="product_edit_category_{{ $tag->tag_id }}">
                                            <td>{{ $tag->tag_id }}</td>
                                            <td> @if($tag->logo)
                                                    <img src="{{ $tag->logo }}"  style="width: 27px;"/>
                                                @endif
                                            </td>
                                            <td><a href="{{ route('ask.tag.index',['id'=>$tag->tag_id]) }}" target="_blank">{{ $tag->name }}</a></td>
                                            <td>{{ implode(',',$tag->tag->categories->pluck('name')->toArray()) }} <a class="btn-edit_category" data-source_id = "{{ $tag->tag_id }}" data-title="{{ $tag->tag->name }}" data-categories="{{ implode(',',$tag->tag->categories->pluck('id')->toArray()) }}" data-toggle="tooltip" title="修改分类"><i class="fa fa-edit"></i></a></td>
                                            <td>{{ $tag->reviews.'|'.$tag->category->name }}</td>
                                            <td width="30%">{{ $tag->summary }}</td>
                                            <td><span class="label @if($tag->status===0) label-warning  @else label-success @endif">{{ trans_common_status($tag->status) }}</span>{{ $tag->created_at }} </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    @if ($tag->status == 0)
                                                        <a class="btn btn-default btn-sm btn-setveriy" data-toggle="tooltip" title="{{ $tag->status ? '设为待审核':'审核成功' }}" data-title="{{ $tag->status ? '设为待审核':'审核成功' }}" data-source_id = "{{ $tag->id }}"><i class="fa {{ $tag->status ? 'fa-lock':'fa-check-square-o' }}"></i></a>
                                                    @endif
                                                    <a class="btn btn-default" href="{{ route('admin.review.submission.create',['id'=>$tag->tag_id]) }}" data-toggle="tooltip" title="添加点评"><i class="fa fa-plus"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.review.product.edit',['id'=>$tag->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                        <a class="btn btn-default btn-sm btn-delete" data-toggle="tooltip" title="删除产品" data-source_id = "{{ $tag->id }}"><i class="fa fa-trash-o"></i></a>
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
                                    <a href="{{ route('admin.review.product.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加产品"><i class="fa fa-plus"></i></a>
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
        <div class="modal fade" id="set_fav_modal" tabindex="-1"  role="dialog" aria-labelledby="set_fav_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exampleModalLabel">修改分类-<span id="title"></span></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tagIds" id="tagIds" />
                        <input type="hidden" name="albumIds" id="albumIds" />
                        <input type="hidden" name="id" id="id" />
                        <div class="box-body">
                            <div class="form-group">
                                <label for="select_tags_id" class="control-label">分类:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select style="width: auto" id="select_tags_id" name="select_tags_id" class="form-control" multiple="multiple" >
                                            @include('admin.category.option',['type'=>'enterprise_review','select_id'=>0,'root'=>false, 'last'=>true])
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="select_tags_id_product_album" class="control-label">专辑:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select style="width: auto" id="select_tags_id_product_album" name="select_tags_id_product_album" class="form-control" multiple="multiple" >
                                            @include('admin.category.option',['type'=>'product_album','select_id'=>0,'root'=>false, 'last'=>true])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="set_fav_submit">确认</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");

        $("#select_tags").select2({
            theme:'bootstrap',
            placeholder: "分类",
            minimumInputLength:2,
            tags:false
        });

        $("#select_tags").change(function(){
            $("#category_id").val($("#select_tags").val());
        });

        $("#select_tags_id").select2({
            theme:'bootstrap',
            placeholder: "分类"
        });

        $("#select_tags_id").change(function(){
            $("#tagIds").val($("#select_tags_id").val());
        });

        $("#select_tags_id_product_album").select2({
            theme:'bootstrap',
            placeholder: "专辑"
        });

        $("#select_tags_id_product_album").change(function(){
            $("#albumIds").val($("#select_tags_id_product_album").val());
        });

        $(".btn-edit_category").click(function(){
            var source_id = $(this).data('source_id');
            var cs = $(this).data('categories');
            $("#id").val(source_id);
            $("#title").html($(this).data('title'));
            $("#select_tags_id").val(cs.toString().split(','));
            $('#select_tags_id').trigger('change');

            $("#select_tags_id_product_album").val(cs.toString().split(','));
            $('#select_tags_id_product_album').trigger('change');
            $('#set_fav_modal').modal('show');
        });

        $("#set_fav_submit").click(function(){
            var id = $("#id").val();
            $.post('/admin/review/product/updateCategory',{ids: id,category_id: $("#tagIds").val(), album_id: $("#albumIds").val()},function(msg){
                window.location.reload();
            });
        });

        $(".btn-setveriy").click(function(){
            var title = $(this).data('title');
            if(!confirm('确认' + title + '？')){
                return false;
            }
            $(this).button('loading');
            var follow_btn = $(this);
            var source_id = $(this).data('source_id');

            $.post('/admin/review/product/setveriy',{id: source_id},function(msg){
                follow_btn.removeClass('disabled');
                follow_btn.removeAttr('disabled');
                if(msg == 'failed') {
                    follow_btn.html('<i class="fa fa-lock"></i>');
                    follow_btn.data('title','设为待审核');
                } else {
                    follow_btn.html('<i class="fa fa-check-square-o"></i>');
                    follow_btn.data('title','审核成功');
                }
            });
        });
        $(".btn-delete").click(function(){
            if(!confirm('确认删除该产品？')){
                return false;
            }
            $(this).button('loading');
            var follow_btn = $(this);
            var source_id = $(this).data('source_id');

            $.post('/admin/review/product/destroy',{ids: source_id},function(msg){
                follow_btn.removeClass('disabled');
                follow_btn.removeAttr('disabled');
                $("#submission_" + source_id).css('display','none');
            });
        });
    </script>
@endsection