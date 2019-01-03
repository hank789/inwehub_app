@extends('admin/public/layout')

@section('title')精选推荐@endsection

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <h1>精选推荐</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="取消推荐选中项" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.cancel_verify') }}','确认取消推荐选中项？')"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除推荐" onclick="confirm_submit('item_form','{{  route('admin.operate.recommendRead.destroy',['id'=>0]) }}', '确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.operate.recommendRead.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" id="tags" name="tags" value="" />
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-4">
                                            <select id="select_tags" name="select_tags" class="form-control" multiple="multiple" >
                                                @if (isset($filter['tags']))
                                                    @foreach( $filter['tags'] as $tag)
                                                        <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-xs-2">
                                            <div>
                                                <label><input type="checkbox" name="withoutTags" value="1" @if ( $filter['withoutTags']??0) checked @endif >无标签</label>
                                                <label><input type="checkbox" name="sortByRate" value="1" @if ( $filter['sortByRate']??0) checked @endif >热度排序</label>
                                            </div>
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
                                        <th>标题</th>
                                        <th>标签语</th>
                                        <th>封面图片</th>
                                        <th>热度</th>
                                        <th>文章ID</th>
                                        <th>标签</th>
                                    </tr>
                                    @php
                                    $pageTags = []
                                    @endphp
                                    @foreach($recommendations as $item)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $item->id }}" name="ids[]"/></td>
                                            <td>{{ $item->id }}</td>
                                            <td>
                                                <a href="{{ $item->getWebUrl() }}" target="_blank">{{ $item->data['title'] }}</a>
                                                <br>{{ $item->created_at }}
                                                <div class="btn-group-xs" >
                                                    <span class="label @if($item->audit_status===0) label-danger  @else label-success @endif">{{ trans_authentication_status($item->audit_status) }}</span>
                                                    <a class="btn btn-default" href="{{ route('admin.operate.recommendRead.edit',['id'=>$item->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-default btn-sm btn-setVerify" data-toggle="tooltip" title="通过审核" data-source_id = "{{ $item->id }}"><i class="fa fa-check-square-o"></i></a>
                                                    <a class="btn btn-default btn-sm btn-cancelVerify" data-toggle="tooltip" title="取消审核" data-source_id = "{{ $item->id }}"><i class="fa fa-lock"></i></a>
                                                </div>
                                            </td>
                                            <td>{{ $item->tips }}</td>
                                            <td>
                                                @if ($item->data['img'] && is_array($item->data['img']))
                                                    @foreach($item->data['img'] as $img)
                                                        <img width="100" height="100" src="{{ $img }}">
                                                    @endforeach
                                                @elseif ($item->data['img'])
                                                    <img width="100" height="100" src="{{ $item->data['img'] ??'' }}">
                                                @endif
                                            </td>
                                            <td>{{ $item->rate }}</td>
                                            <td>{{ $item->source_id }}</td>
                                            <td>
                                                @php
                                                    $pageTags += $item->tags->pluck('name','id')->toArray()
                                                @endphp
                                                {{ implode(',',$item->tags->pluck('name')->toArray()) }}
                                                <a class="btn-edit_category" data-source_id = "{{ $item->id }}" data-title="{{ $item->data['title'] }}" data-categories="{{ implode(',',$item->tags->pluck('id')->toArray()) }}" data-toggle="tooltip" title="修改标签"><i class="fa fa-edit"></i></a>
                                            </td>
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
                                    <span class="total-num">共 {{ $recommendations->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $recommendations->appends($filter)->render()) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="set_fav_modal" tabindex="-1"  role="dialog" aria-labelledby="set_fav_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">修改标签-<span id="title"></span></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tagIds" id="tagIds" />
                    <input type="hidden" name="id" id="id" />
                    <div class="box-body">
                        <div class="form-group">
                            <label for="select_tags_id" class="control-label">标签:</label>
                            <div class="row">
                                <div class="col-sm-10">
                                    <select style="width: auto" id="select_tags_id" name="select_tags_id" class="form-control" multiple="multiple" >
                                        @foreach($pageTags as $key=>$name)
                                            <option value="{{ $key }}">{{ $name }}</option>
                                        @endforeach
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
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.recommendRead.index') }}");
        $(".btn-setVerify").click(function(){
            if(!confirm('确认审核通过该文章？')){
                return false;
            }
            $(this).button('loading');
            var follow_btn = $(this);
            var source_id = $(this).data('source_id');
            $.post('/admin/recommendRead/verify',{ids: [source_id]},function(msg){
                follow_btn.html('已审核');
            });
        });
        $(".btn-cancelVerify").click(function(){
            if(!confirm('确认取消该文章的精选推荐？')){
                return false;
            }
            $(this).button('loading');
            var follow_btn = $(this);
            var source_id = $(this).data('source_id');

            $.post('/admin/recommendRead/cancel_verify',{ids: [source_id]},function(msg){
                follow_btn.html('已取消');
            });
        });
        $("#select_tags").select2({
            theme:'bootstrap',
            placeholder: "标签",
            ajax: {
                url: '/manager/ajax/loadTags',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        word: params.term,
                        type: 6
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength:2,
            tags:false
        });

        $("#select_tags").change(function(){
            $("#tags").val($("#select_tags").val());
        });


        $("#change_tags_submit").click(function(){
            var ids = new Array();
            $("#item_form input[name='ids[]']:checkbox").each(function(i){
                if(true == $(this).is(':checked')){
                    ids.push($(this).val());
                }
            });

            if( ids.length > 0 ){
                $("#change_tags_from input[name='rids']").val(ids.join(","));
                $("#change_tags_from").submit();
            }else{
                alert("您没有选中任何内容");
            }
        });

        $("#select_tags_id").select2({
            theme:'bootstrap',
            placeholder: "标签",
            ajax: {
                url: '/manager/ajax/loadTags',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        word: params.term,
                        type: 'all'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength:2,
            tags:false
        });

        $("#select_tags_id").change(function(){
            $("#tagIds").val($("#select_tags_id").val());
        });

        $(".btn-edit_category").click(function(){
            var source_id = $(this).data('source_id');
            var cs = $(this).data('categories');
            $("#id").val(source_id);
            $("#title").html($(this).data('title'));
            $("#select_tags_id").val(cs.toString().split(','));
            $('#select_tags_id').trigger('change');
            $('#set_fav_modal').modal('show');
        });

        $("#set_fav_submit").click(function(){
            var id = $("#id").val();
            $.post('/admin/recommendRead/changeTags',{id: id,tagIds: $("#tagIds").val()},function(msg){
                window.location.reload()
            });
            $('#set_fav_modal').modal('hide');
        });
    </script>
@endsection