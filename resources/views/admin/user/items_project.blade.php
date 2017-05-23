@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('title'){{ $title }}@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $title }}
            <small>{{ $title }}</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_base" data-toggle="tab" aria-expanded="false">添加{{ $title }}</a></li>
                        <li><a href="#tab_news" data-toggle="tab" aria-expanded="true">列表</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_base">
                            <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.user.item.store') }}">
                                <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" id="industry_tags" name="industry_tags" value="" />
                                <input type="hidden" id="product_tags" name="product_tags" value="" />
                                <input type="hidden" name="id" value="{{ $object_item->id }}" />
                                <input type="hidden" name="type" value="{{ $type }}" />
                                <input type="hidden" name="user_id" value="{{ $user_id }}" />


                                <div class="form-group @if($errors->has('project_name')) has-error @endif ">
                                    <label for="project_name">项目名称:</label>
                                    <input id="project_name" type="text" name="project_name" required  class="form-control input-lg" placeholder="" value="{{ old('project_name',$object_item->project_name) }}" />
                                    @if($errors->has('project_name')) <p class="help-block">{{ $errors->first('project_name') }}</p> @endif
                                </div>

                                <div class="form-group @if($errors->has('title')) has-error @endif ">
                                    <label for="title">项目职位:</label>
                                    <input id="title" type="text" name="title" required  class="form-control input-lg" placeholder="" value="{{ old('title',$object_item->title) }}" />
                                    @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                                </div>

                                <div class="form-group @if($errors->has('customer_name')) has-error @endif ">
                                    <label for="customer_name">客户名称:</label>
                                    <input id="customer_name" type="text" name="customer_name" required  class="form-control input-lg" placeholder="" value="{{ old('customer_name',$object_item->customer_name) }}" />
                                    @if($errors->has('customer_name')) <p class="help-block">{{ $errors->first('customer_name') }}</p> @endif
                                </div>

                                <div class="form-group @if ($errors->first('industry_tags')) has-error @endif">
                                    <label for="select_industry_tags" class="control-label">所在行业</label>
                                    <div class="row">
                                        <div class="col-sm-10">
                                            <select id="select_industry_tags" name="select_industry_tags" class="form-control" multiple="multiple" >
                                                @if($object_item->id)
                                                    @foreach( $object_item->tags()->where('category_id',9)->get() as $tag)
                                                        <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if ($errors->first('industry_tags'))
                                                <span class="help-block">{{ $errors->first('industry_tags') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group @if ($errors->first('product_tags')) has-error @endif">
                                    <label for="select_product_tags" class="control-label">产品类型</label>
                                    <div class="row">
                                        <div class="col-sm-10">
                                            <select id="select_product_tags" name="select_product_tags" class="form-control" multiple="multiple" >
                                                @if($object_item->id)
                                                    @foreach( $object_item->tags()->where('category_id',10)->get() as $tag)
                                                        <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if ($errors->first('product_tags'))
                                                <span class="help-block">{{ $errors->first('product_tags') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="setting-city" class="control-label">工作时间</label>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" name="begin_time" class="form-control" placeholder="开始日期:Y-m 或 至今" value="{{ old('begin_time',$object_item->begin_time) }}" />
                                            @if ($errors->has('begin_time')) <p class="help-block">{{ $errors->first('begin_time') }}</p> @endif
                                        </div>
                                        <div class="col-sm-1">
                                            至
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" name="end_time" class="form-control" placeholder="结束日期:Y-m" value="{{ old('end_time',$object_item->end_time) }}" />
                                            @if ($errors->has('end_time')) <p class="help-block">{{ $errors->first('end_time') }}</p> @endif
                                        </div>
                                        <div class="col-sm-3">
                                            <span class="text-muted">(输入"至今"表示到现在)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="editor">描述：</label>
                                    <textarea name="description" class="form-control">{{ $object_item->description }}</textarea>
                                </div>

                                <div class="row mt-20">
                                    <div class="col-xs-12 col-md-1">
                                        <button type="submit" class="btn btn-primary pull-right editor-submit">提交</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="tab_news">
                            <form role="form" name="newsForm" id="item_form" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id" value="{{ $object_item->id }}" />
                                <input type="hidden" name="type" value="{{ $type }}" />
                                <input type="hidden" name="user_id" value="{{ $user_id }}" />
                                <div class="box-body">
                                    <h4>{{ $title }}列表</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="topic_news_table" style="width: 100%;">
                                            <thead>
                                            <tr>
                                                <th><input type="checkbox" class="checkbox-toggle" /></th>
                                                <th>项目名称</th>
                                                <th>项目职位</th>
                                                <th>客户名称</th>
                                                <th>开始日期</th>
                                                <th>结束日期</th>
                                                <th>行业标签</th>
                                                <th>产品标签</th>
                                                <th>描述</th>
                                                <th>操作</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" /></td>
                                                    <td>{{ $item->project_name }}</td>
                                                    <td>{{ $item->title }}</td>
                                                    <td>{{ $item->customer_name }}</td>
                                                    <td>{{ $item->begin_time }}</td>
                                                    <td>{{ $item->end_time }}</td>
                                                    <td>{{ implode(',',$item->tags()->where('category_id',9)->pluck('name')->toArray()) }}</td>
                                                    <td>{{ implode(',',$item->tags()->where('category_id',10)->pluck('name')->toArray()) }}</td>
                                                    <td>{{ $item->description }}</td>
                                                    <td>
                                                        <div class="btn-group-xs" >
                                                            <a class="btn btn-default" href="{{ route('admin.user.item.info',['item_id'=>$item->id,'type'=>$type,'user_id'=>$item->user->id]) }}" data-toggle="tooltip" title="修改"><i class="fa fa-edit"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button class="btn btn-primary" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.user.item.destroy') }}','确认删除选中项？')">删除选中项</button>
                                </div>
                            </form>
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
        $(function(){

            $("#select_industry_tags").select2({
                theme:'bootstrap',
                placeholder: "所在行业",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 3
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

            $("#select_industry_tags").change(function(){
                $("#industry_tags").val($("#select_industry_tags").val());
                //alert('industry_tags:'+$("#industry_tags").val());
            });

            $("#select_product_tags").select2({
                theme:'bootstrap',
                placeholder: "产品标签",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 4
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

            $("#select_product_tags").change(function(){
                $("#product_tags").val($("#select_product_tags").val());
                //alert('product_tags:'+$("#product_tags").val());
            });

            set_active_menu('manage_user',"{{ route('admin.user.index') }}");
        });

    </script>
@endsection