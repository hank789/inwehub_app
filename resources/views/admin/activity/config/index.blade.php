@extends('admin/public/layout')
@section('title')活动设置@endsection
@section('content')
    <section class="content-header">
        <h1>
            活动设置
            <small>网站活动策略设置</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form role="form" name="addForm" method="POST" action="{{ route('admin.activity.config') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="alert alert-info" role="alert"></div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dataTable text-center">
                                    <thead>
                                    <tr role="row">
                                        <th>活动名称</th>
                                        <th>开始时间</th>
                                        <th>结束时间</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <tr>
                                        <td>首次提问1元活动</td>
                                        <td>
                                            <div class="col-md-8 col-md-offset-2 @if ($errors->has('ac_first_ask_begin_time')) has-error @endif "><input type="text" class="form-control" name="ac_first_ask_begin_time" placeholder="2017-09-09 14:00" value="{{ old('ac_first_ask_begin_time',Setting()->get('ac_first_ask_begin_time')) }}" /></div>
                                        </td>
                                        <td>
                                            <div class="col-md-8 col-md-offset-2 @if ($errors->has('ac_first_ask_end_time')) has-error @endif "><input type="text" class="form-control" name="ac_first_ask_end_time" placeholder="2017-09-19 14:00" value="{{ old('ac_first_ask_end_time',Setting()->get('ac_first_ask_end_time')) }}" /></div>
                                            @if($errors->has('ac_first_ask_end_time')) <p class="help-block">{{ $errors->first('ac_first_ask_end_time') }}</p> @endif

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
        set_active_menu('activity',"{{ route('admin.activity.config') }}");
    </script>
@endsection