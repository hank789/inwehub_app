@extends('admin/public/layout')
@section('title')编辑分类@endsection
@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection
@section('content')
    <section class="content-header">
        <h1>编辑分类</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.category.update',['id'=>$category->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" id="parent_id" name="parent_id" value="{{ $category->parent_id }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('name')) has-error @endif">
                                <label>分类名称</label>
                                <input type="text" name="name" class="form-control " placeholder="分类名称" value="{{ old('name',$category->name) }}">
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('slug')) has-error @endif">
                                <label>分类标识</label>
                                <span class="text-muted">(英文字母)</span>
                                <input type="text" name="slug" class="form-control " placeholder="分类标识" value="{{ old('slug',$category->slug) }}">
                                @if($errors->has('slug')) <p class="help-block">{{ $errors->first('slug') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>选择父级分类</label>
                                <select name="select_tags" id="select_tags" class="form-control">
                                    <option value="0">选择父级分类</option>
                                    @include('admin.category.option',['type'=>'all','select_id'=>$category->parent_id,'root'=>false])
                                </select>
                            </div>

                            <div class="form-group @if($errors->has('sort')) has-error @endif">
                                <label>排序</label>
                                <span class="text-muted">(仅对当前层级分类有效)</span>
                                <input type="text" name="sort" class="form-control " placeholder="排序" value="{{ old('sort',$category->sort) }}">
                                @if($errors->has('sort')) <p class="help-block">{{ $errors->first('sort') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>图标</label>
                                <input type="file" name="icon" />
                                @if($category->icon)
                                    <div style="margin-top: 10px;">
                                        <img src="{{ $category->icon }}" />
                                    </div>
                                @endif
                            </div>

                            <div class="form-group @if ($errors->has('summary')) has-error @endif">
                                <label for="name">简介</label>
                                <textarea name="summary" class="form-control" placeholder="话题简介" style="height: 80px;">{{ old('summary',$category->summary) }}</textarea>
                                @if ($errors->has('summary')) <p class="help-block">{{ $errors->first('summary') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <span class="text-muted">(禁用后前台不会显示)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="1" {{ $category->status==1?'checked':'' }} /> 启用
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="0" {{ $category->status==0?'checked':'' }} /> 禁用
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">保存</button>
                            <button type="reset" class="btn btn-success">重置</button>
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
        set_active_menu('manage_tags',"{{ route('admin.category.index') }}");
        $(function(){
            var parent_id = "{{ $category->parent_id }}";
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