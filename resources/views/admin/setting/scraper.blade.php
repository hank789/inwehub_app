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