@extends('admin/public/layout')
@section('title')产品管理@endsection
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
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.review.product.create') }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="添加产品"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.review.product.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-3">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-3">
                                            <select class="form-control" name="category_id">
                                                <option value="-1">不选择</option>
                                                @include('admin.category.option',['type'=>'enterprise_review','select_id'=>$filter['category_id'],'root'=>false, 'last'=>true])
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
                                        <tr>
                                            <td>{{ $tag->tag_id }}</td>
                                            <td> @if($tag->logo)
                                                    <img src="{{ $tag->logo }}"  style="width: 27px;"/>
                                                @endif
                                            </td>
                                            <td><a href="{{ route('ask.tag.index',['id'=>$tag->tag_id]) }}" target="_blank">{{ $tag->name }}</a></td>
                                            <td>{{ implode(',',$tag->tag->categories->pluck('name')->toArray()) }}</td>
                                            <td>{{ $tag->reviews }}</td>
                                            <td width="30%">{{ $tag->summary }}</td>
                                            <td><span class="label @if($tag->status===0) label-warning  @else label-success @endif">{{ trans_common_status($tag->status) }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    @if ($tag->status == 0)
                                                        <a class="btn btn-default btn-sm btn-setveriy" data-toggle="tooltip" title="{{ $tag->status ? '设为待审核':'审核成功' }}" data-title="{{ $tag->status ? '设为待审核':'审核成功' }}" data-source_id = "{{ $tag->id }}"><i class="fa {{ $tag->status ? 'fa-lock':'fa-check-square-o' }}"></i></a>
                                                    @endif
                                                    <a class="btn btn-default" href="{{ route('admin.review.submission.create',['id'=>$tag->tag_id]) }}" data-toggle="tooltip" title="添加点评"><i class="fa fa-plus"></i></a>
                                                    <a class="btn btn-default" href="{{ route('admin.review.product.edit',['id'=>$tag->tag_id,'cid'=>$tag->category_id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
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
    </section>

@endsection

@section('script')
    @include("admin.public.change_category_modal",['type'=>'tags','form_id'=>'item_form','form_action'=>route('admin.tag.changeCategories')])
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");
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
    </script>
@endsection