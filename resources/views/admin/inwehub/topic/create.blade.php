@extends('admin/public/layout')

@section('title')新建话题@endsection

@section('content')
    <section class="content-header">
        <h1>
            新建话题
            <small>添加新话题</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.inwehub.topic.store') }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags" name="tags" value="" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('title')) has-error @endif ">
                                <label for="title">标题:</label>
                                <input id="title" type="text" name="title"  class="form-control input-lg" placeholder="我想起那天下午在夕阳下的奔跑,那是我逝去的青春" value="{{ old('title','') }}" />
                                @if($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="editor">话题导读：</label>
                                <textarea name="summary" class="form-control" placeholder="话题摘要">{{ old('summary','') }}</textarea>
                            </div>
                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">发布话题</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_inwehub',"{{ route('admin.inwehub.topic.index') }}");
    </script>
@endsection