@extends('admin.public.layout')

@section('title')新建小程序定制化客户@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            新建小程序定制化客户
            <small>新建小程序定制化客户认证</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.partner.oauth.store') }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="tags" name="tags" value="" />
                        <div class="box-body">
                            <div class="form-group @if($errors->has('app_id')) has-error @endif ">
                                <label for="app_version">客户唯一标示:</label>
                                <input id="app_version" type="text" name="app_id"  class="form-control input-lg" placeholder="客户唯一标书，字母和数字组合" value="{{ old('app_id','') }}" />
                                @if($errors->has('app_id')) <p class="help-block">{{ $errors->first('app_id') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="author_id_select" class="control-label">产品</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_tags" name="select_tags" class="form-control" >
                                        </select>
                                        @if($errors->has('tags')) <p class="help-block">{{ $errors->first('tags') }}</p> @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if($errors->has('description')) has-error @endif">
                                <label for="update_msg">客户描述：</label>
                                <textarea name="description" class="form-control " placeholder="客户描述">{{ old('description','') }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label for="is_force">状态：</label>
                                <div class="radio">
                                    <label><input type="radio" name="status" value="0" checked >待审核</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="status" value="1">已审核</label>&nbsp;&nbsp;
                                </div>
                            </div>


                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">创建新客户</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('/js/global.js')}}"></script>
    <script type="text/javascript">
        set_active_menu('manage_partner',"{{ route('admin.partner.oauth.index') }}");
    </script>
@endsection