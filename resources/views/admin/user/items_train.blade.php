@extends('admin/public/layout')

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


                                <div class="form-group @if($errors->has('certificate')) has-error @endif ">
                                    <label for="certificate">证书名称:</label>
                                    <input id="certificate" type="text" name="certificate" required  class="form-control input-lg" placeholder="" value="{{ old('certificate',$object_item->certificate) }}" />
                                    @if($errors->has('certificate')) <p class="help-block">{{ $errors->first('certificate') }}</p> @endif
                                </div>

                                <div class="form-group @if($errors->has('agency')) has-error @endif ">
                                    <label for="agency">认证机构:</label>
                                    <input id="agency" type="text" name="agency" required  class="form-control input-lg" placeholder="" value="{{ old('agency',$object_item->agency) }}" />
                                    @if($errors->has('agency')) <p class="help-block">{{ $errors->first('agency') }}</p> @endif
                                </div>

                                <div class="form-group">
                                    <label for="setting-city" class="control-label">获取日期</label>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" name="get_time" class="form-control" placeholder="获取日期:Y-m" value="{{ old('get_time',$object_item->get_time) }}" />
                                            @if ($errors->has('get_time')) <p class="help-block">{{ $errors->first('get_time') }}</p> @endif
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
                                    <h4>工作经历列表</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="topic_news_table" style="width: 100%;">
                                            <thead>
                                            <tr>
                                                <th><input type="checkbox" class="checkbox-toggle" /></th>
                                                <th>证书名称</th>
                                                <th>认证机构</th>
                                                <th>获取日期</th>
                                                <th>描述</th>
                                                <th>操作</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" /></td>
                                                    <td>{{ $item->certificate }}</td>
                                                    <td>{{ $item->agency }}</td>
                                                    <td>{{ $item->get_time }}</td>
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
    <script src="{{ asset('/static/js/autosize.min.js')}}"></script>

    <script type="text/javascript">
        $(function(){
            autosize($('textarea'));
            set_active_menu('manage_user',"{{ route('admin.user.index') }}");
        });

    </script>
@endsection