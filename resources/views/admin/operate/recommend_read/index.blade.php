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
                                    <button class="btn btn-default btn-sm" title="移动标签"  data-toggle="modal" data-target="#change_tags_modal" ><i data-toggle="tooltip" title="移动标签" class="fa fa-bars" aria-hidden="true"></i></button>
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
                                        <th>排序</th>
                                        <th>标签</th>
                                    </tr>
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
                                            <td>{{ $item->sort }}</td>
                                            <td>
                                                @foreach($item->tags as $tagInfo)
                                                    {{ $tagInfo->name.',' }}
                                                @endforeach
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
    <div class="modal fade" id="change_tags_modal" tabindex="-1"  role="dialog" aria-labelledby="change_tags_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">赋予标签</h4>
                </div>
                <div class="modal-body">
                    <form role="form" name="categoryForm" id="change_tags_from" method="POST" action="{{ route('admin.operate.recommendRead.changeTags') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="tagIds" id="tagIds" />
                        <input type="hidden" name="rids" id="rids" />
                        <div class="box-body">
                            <div class="form-group">
                                <label for="select_tags_id" class="control-label">将选中项目移动到:</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select style="width: auto" id="select_tags_id" name="select_tags_id" class="form-control" multiple="multiple" >
                                            @foreach($tags as $tag)
                                                <option value="{{ $tag['id'] }}">{{ $tag['text'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="change_tags_submit">确认</button>
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

        $("#select_tags_id").select2({
            theme:'bootstrap',
            placeholder: "标签"
        });

        $("#select_tags_id").change(function(){
            $("#tagIds").val($("#select_tags_id").val());
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
    </script>
@endsection