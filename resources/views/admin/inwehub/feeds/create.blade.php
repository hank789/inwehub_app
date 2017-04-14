@extends('admin/public/layout')

@section('title')新建数据源@endsection

@section('content')
    <section class="content-header">
        <h1>
            新建数据源
            <small>添加数据源</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.inwehub.feeds.store') }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags" name="tags" value="" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="name">站点名:</label>
                                <input id="name" type="text" name="name"  class="form-control input-lg" placeholder="" value="{{ old('name','') }}" />
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="description">描述：</label>
                                <input id="description" type="text" name="description"  class="form-control input-lg" placeholder="" value="{{ old('description','') }}" />
                            </div>
                            <div class="form-group">
                                <label for="source_type">源类型：</label>
                                <div class="radio">
                                    <label><input type="radio" name="source_type" value="1" checked >RSS</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="source_type" value="2">ATOM</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="source_link">数据源url：</label>
                                <input id="source_link" type="text" name="source_link"  class="form-control input-lg" placeholder="" value="{{ old('source_link','') }}" />
                            </div>
                        </div>

                        <div class="box-footer">
                            <div class="col-xs-12 col-md-1">
                                <button type="submit" class="btn btn-primary pull-right editor-submit">发布数据源</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.feeds.index') }}");
    </script>
@endsection