@extends('admin/public/layout')
@section('title')启动页设置@endsection
@section('content')
<section class="content-header">
    <h1>
        启动页设置
        <small>启动页设置</small>
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <form role="form" name="addForm" method="POST" action="{{ route('admin.operate.bootGuide') }}">
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
                                    <td>是否开启启动页</td>
                                    <td>
                                        <div class="col-md-4 col-md-offset-4 @if ($errors->has('show_boot_guide')) has-error @endif ">
                                            <div class="radio">
                                                <label><input type="radio" name="show_boot_guide" value="0" @if ( Setting()->get('show_boot_guide',0) == 0) checked @endif >开启</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label><input type="radio" name="show_boot_guide" value="1" @if ( Setting()->get('show_boot_guide',0) == 1) checked @endif>关闭</label>&nbsp;&nbsp;&nbsp;&nbsp;
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
    set_active_menu('operate',"{{ route('admin.operate.bootGuide') }}");
</script>
@endsection