@extends('admin/public/layout')
@section('title')
    添加商品
@endsection
@section('content')
    <section class="content-header">
        <h1>
            商品管理
            <small>添加商品</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <form role="form" name="addForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.goods.store') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">

                            <div class="form-group">
                                <label>是否需要邮寄</label>
                                <span class="text-muted">(虚拟物品不用邮寄例。如：手机充值等)</span>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="post_type" value="1" checked /> 是
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="post_type" value="0" /> 否
                                    </label>
                                </div>
                            </div>

                            <div class="form-group @if($errors->has('name')) has-error @endif">
                                <label>商品名称</label>
                                <input type="text" name="name" class="form-control " placeholder="商品名称" value="{{ old('name','') }}">
                                @if($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>logo图片</label>
                                <input type="file" name="logo" />
                            </div>

                            <div class="form-group @if($errors->has('remnants')) has-error @endif">
                                <label>商品总数量</label>
                                <input type="text" name="remnants" class="form-control " placeholder="商品总数量" value="{{ old('remnants','') }}">
                                @if($errors->has('remnants')) <p class="help-block">{{ $errors->first('remnants') }}</p> @endif
                            </div>


                            <div class="form-group @if($errors->has('coins')) has-error @endif">
                                <label>商品价格（消耗的金币数）</label>
                                <input type="text" name="coins" class="form-control " placeholder="消耗的金币数" value="{{ old('coins','') }}">
                                @if($errors->has('coins')) <p class="help-block">{{ $errors->first('coins') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('description')) has-error @endif">
                                <label>商品详情</label>
                                <textarea name="description" class="form-control" placeholder="话题简介" style="height: 80px;">{{ old('description','') }}</textarea>
                                @if($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
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
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.notice.index') }}");
    </script>
@endsection
