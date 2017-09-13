@extends('admin.public.layout')

@section('title')修改APP版本@endsection

@section('content')
    <section class="content-header">
        <h1>
             修改APP版本
            <small>修改APP版本</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.appVersion.update',['id'=>$version->id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if($errors->has('app_version')) has-error @endif ">
                                <label for="app_version">版本号:</label>
                                <input id="app_version" type="text" name="app_version"  class="form-control input-lg" placeholder="3位APP版本号,如:1.0.0" value="{{ old('app_version',$version->app_version) }}" />
                                @if($errors->has('app_version')) <p class="help-block">{{ $errors->first('app_version') }}</p> @endif
                            </div>

                            <div class="form-group @if($errors->has('package_url')) has-error @endif">
                                <label for="package_url">下载地址：</label>
                                <input id="package_url" type="text" name="package_url"  class="form-control input-lg" placeholder="包的更新地址,以.wgt结尾" value="{{ old('package_url',$version->package_url) }}" />
                                @if($errors->has('package_url')) <p class="help-block">{{ $errors->first('package_url') }}</p> @endif
                            </div>
                            <div class="form-group">
                                <label for="is_force">是否ios强更：</label>
                                <div class="radio">
                                    <label><input type="radio" name="is_ios_force" value="0" @if ( $version->is_ios_force == 0) checked @endif >不强更(热更新,用户无感知)</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="is_ios_force" value="1" @if ( $version->is_ios_force == 1) checked @endif>强更(需要强制跳转到App store下载)</label>&nbsp;&nbsp;
                                    <label><input type="radio" name="is_ios_force" value="2" @if ( $version->is_ios_force == 2) checked @endif>不更新</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="is_force">是否android强更：</label>
                                <div class="radio">
                                    <label><input type="radio" name="is_android_force" value="0" @if ( $version->is_android_force == 0) checked @endif >不强更(热更新,用户无感知)</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="is_android_force" value="1" @if ( $version->is_android_force == 1) checked @endif>强更(需要强制跳转到安卓市场下载)</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="is_android_force" value="2" @if ( $version->is_android_force == 2) checked @endif>不更新</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>

                            <div class="form-group @if($errors->has('update_msg')) has-error @endif">
                                <label for="update_msg">更新日志：</label>
                                <textarea name="update_msg" class="form-control " placeholder="更新日志">{{ old('update_msg',$version->update_msg) }}</textarea>
                                @if ($errors->has('update_msg')) <p class="help-block">{{ $errors->first('update_msg') }}</p> @endif
                            </div>

                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">提交修改</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.appVersion.index') }}");
    </script>
@endsection