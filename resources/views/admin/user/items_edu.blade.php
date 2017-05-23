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


                                <div class="form-group @if($errors->has('school')) has-error @endif ">
                                    <label for="school">学校:</label>
                                    <input id="school" type="text" name="school" required  class="form-control input-lg" placeholder="" value="{{ old('school',$object_item->school) }}" />
                                    @if($errors->has('school')) <p class="help-block">{{ $errors->first('school') }}</p> @endif
                                </div>

                                <div class="form-group @if($errors->has('major')) has-error @endif ">
                                    <label for="major">专业:</label>
                                    <input id="major" type="text" name="major" required  class="form-control input-lg" placeholder="" value="{{ old('major',$object_item->major) }}" />
                                    @if($errors->has('major')) <p class="help-block">{{ $errors->first('major') }}</p> @endif
                                </div>

                                <div class="form-group ">
                                    <label for="degree">学位</label>
                                    <div class="radio">
                                        <label><input type="radio" name="degree" value="本科" @if ( $object_item->degree === '本科') checked @endif >本科</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="degree" value="硕士" @if ( $object_item->degree === '硕士') checked @endif >硕士</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="degree" value="大专" @if ( $object_item->degree === '大专') checked @endif >大专</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="degree" value="博士" @if ( $object_item->degree === '博士') checked @endif >博士</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <label><input type="radio" name="degree" value="其它" @if ( $object_item->degree === '其它') checked @endif >其它</label>&nbsp;&nbsp;&nbsp;&nbsp;
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
                                                <th>学校</th>
                                                <th>专业</th>
                                                <th>学历</th>
                                                <th>开始日期</th>
                                                <th>结束日期</th>
                                                <th>描述</th>
                                                <th>操作</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" /></td>
                                                    <td>{{ $item->school }}</td>
                                                    <td>{{ $item->major }}</td>
                                                    <td>{{ $item->degree }}</td>
                                                    <td>{{ $item->begin_time }}</td>
                                                    <td>{{ $item->end_time }}</td>
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
    <script type="text/javascript">
        $(function(){
            set_active_menu('manage_user',"{{ route('admin.user.index') }}");
        });

    </script>
@endsection