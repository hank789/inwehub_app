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
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.scraper.feeds.store') }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags" name="tags" value="" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif ">
                                <label for="name">站点名:</label>
                                <input id="name" type="text" name="name"  class="form-control input-lg" placeholder="" value="{{ old('name','') }}" />
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('group_id')) has-error @endif ">
                                <label for="wx_hao">圈子id:</label>
                                <input id="group_id" type="text" name="group_id"  class="form-control input-lg" placeholder="该数据源文章所属的圈子id" value="{{ old('group_id',0) }}" />
                                @if($errors->has('group_id')) <p class="help-block">{{ $errors->first('group_id') }}</p> @endif
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
                                <button type="submit" class="btn btn-primary editor-submit">发布数据源</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_scraper',"{{ route('admin.scraper.feeds.index') }}");
    </script>
@endsection