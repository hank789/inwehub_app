@extends('admin/public/layout')
@section('title')
    添加分类
@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            分类管理
            <small>添加分类</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="addForm" method="POST"  action="{{ route('admin.category.store') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="parent_id" name="parent_id" value="" />
                        <div class="box-body">

                            <div class="form-group @if($errors->has('name')) has-error @endif">
                                <label>分类名称</label>
                                <input type="text" name="name" class="form-control " placeholder="分类名称" value="{{ old('name','') }}">
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('slug')) has-error @endif">
                                <label>分类标识</label>
                                <span class="text-muted">(英文字母)</span>
                                <input type="text" name="slug" class="form-control " placeholder="分类标识" value="{{ old('slug','') }}">
                                @if($errors->has('slug')) <p class="help-block">{{ $errors->first('slug') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>选择父级分类</label>
                                <select name="select_tags" id="select_tags" class="form-control">
                                    <option value="0">选择父级分类</option>
                                    @include('admin.category.option',['type'=>'all','select_id'=>0, 'root'=>false])
                                </select>
                            </div>

                            <div class="form-group @if($errors->has('sort')) has-error @endif">
                                <label>排序</label>
                                <span class="text-muted">(仅对当前层级分类有效)</span>
                                <input type="text" name="sort" class="form-control " placeholder="排序" value="{{ old('sort','') }}">
                                @if($errors->has('sort')) <p class="help-block">{{ $errors->first('sort') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>图标</label>
                                <input type="file" name="icon" />
                            </div>

                            <div class="form-group @if ($errors->has('summary')) has-error @endif">
                                <label for="name">简介</label>
                                <textarea name="summary" class="form-control" placeholder="话题简介" style="height: 80px;">{{ old('summary','') }}</textarea>
                                @if ($errors->has('summary')) <p class="help-block">{{ $errors->first('summary') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <span class="text-muted">(禁用后前台不会显示)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="1" checked /> 启用
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="0" /> 禁用
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            set_active_menu('manage_tags',"{{ route('admin.category.index') }}");
            $("#select_tags").select2({
                theme:'bootstrap',
                placeholder: "分类",
                minimumInputLength:2,
                tags:false
            });

            $("#select_tags").change(function(){
                $("#parent_id").val($("#select_tags").val());
            });
        });
    </script>
@endsection
