@extends('admin/public/layout')
@section('title')问答设置@endsection
@section('content')
<section class="content-header">
    <h1>
        抓取设置
        <small>抓取设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.setting.scraper') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dataTable text-center">
                                <thead>
                                <tr role="row">
                                    <th>参数</th>
                                    <th>数值</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr>
                                    <td>微信公众号文章自动发布</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('is_scraper_wechat_auto_publish')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="is_scraper_wechat_auto_publish" value="0" @if ( Setting()->get('is_scraper_wechat_auto_publish',1) == 0) checked @endif >否</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="is_scraper_wechat_auto_publish" value="1" @if ( Setting()->get('is_scraper_wechat_auto_publish',1) == 1) checked @endif>是</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>招标信息关键词(多个以|隔开)</td>
                                    <td>
                                        <div class="col-md-12 @if ($errors->has('scraper_bid_keywords')) has-error @endif ">
                                            <input type="text" class="form-control" name="scraper_bid_keywords" value="{{ old('scraper_bid_keywords',Setting()->get('scraper_bid_keywords','SAP|信息化|供应链金融|供应链管理|供应链|平台|oracle|管理咨询|麦肯锡')) }}" /></div>
                                        @if($errors->has('scraper_bid_keywords')) <p class="help-block">{{ $errors->first('scraper_bid_keywords') }}</p> @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td>剑鱼站点cookie(多个以"||"相隔)</td>
                                    <td>
                                        <div class="col-md-12 @if ($errors->has('scraper_jianyu360_cookie')) has-error @endif ">
                                            <input type="text" class="form-control" name="scraper_jianyu360_cookie" value="{{ old('scraper_jianyu360_cookie',Setting()->get('scraper_jianyu360_cookie','')) }}" /></div>
                                        @if($errors->has('scraper_jianyu360_cookie')) <p class="help-block">{{ $errors->first('scraper_jianyu360_cookie') }}</p> @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>剑鱼APP cookie(使用抓包软件获得，多个以"||"相隔)</td>
                                    <td>
                                        <div class="col-md-12 @if ($errors->has('scraper_jianyu360_app_cookie')) has-error @endif ">
                                            <input type="text" class="form-control" name="scraper_jianyu360_app_cookie" value="{{ old('scraper_jianyu360_app_cookie',Setting()->get('scraper_jianyu360_app_cookie','')) }}" /></div>
                                        @if($errors->has('scraper_jianyu360_app_cookie')) <p class="help-block">{{ $errors->first('scraper_jianyu360_app_cookie') }}</p> @endif
                                    </td>
                                </tr>
                                </tbody>
                            </table>
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
<script type="text/javascript">
    set_active_menu('global',"{{ route('admin.setting.scraper') }}");
</script>
@endsection