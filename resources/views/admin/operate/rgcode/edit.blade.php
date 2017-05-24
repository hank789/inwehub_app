@extends('admin.public.layout')

@section('title')修改邀请码@endsection

@section('content')
    <section class="content-header">
        <h1>
             修改邀请码
            <small>修改邀请码</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.operate.rgcode.update',['id'=>$code->id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('keyword')) has-error @endif ">
                                <label for="keyword">邀请对象:</label>
                                <input id="keyword" type="text" name="keyword"  class="form-control input-lg" placeholder="邀请对象" value="{{ old('keyword',$code->keyword) }}" />
                                @if($errors->has('keyword')) <p class="help-block">{{ $errors->first('keyword') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('code')) has-error @endif">
                                <label for="code">邀请码：</label>
                                <input id="code" type="text" name="code"  class="form-control input-lg" placeholder="6位邀请码" value="{{ old('code',$code->code) }}" />
                                @if($errors->has('code')) <p class="help-block">{{ $errors->first('code') }}</p> @endif
                            </div>
                            <div class="form-group">
                                <label for="status">状态：</label>
                                <div class="radio">
                                    <label><input type="radio" name="status" value="0" @if ( $code->status == 0) checked @endif>未生效</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="status" value="1" @if ( $code->status == 1) checked @endif>已生效</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary editor-submit">修改邀请码</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.rgcode.index') }}");
    </script>
@endsection