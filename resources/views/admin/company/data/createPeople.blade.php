@extends('admin/public/layout')

@section('content')
    <section class="content-header">
        <h1>
            添加企业相关人员
            <small>添加企业相关人员</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Tables</a></li>
            <li class="active">Simple</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                @include('admin/public/error')
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">基本信息</h3>
                    </div>
                    <form role="form" name="addForm" method="POST" action="{{ route('admin.company.data.storePeople') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>公司id</label>
                                <input type="text" name="company_data_id" class="form-control "  placeholder="公司id" value="{{ old('company_data_id',$data_id) }}">
                            </div>

                            <div class="form-group">
                                <label>用户id</label>
                                <input type="text" name="user_id" class="form-control "  placeholder="" value="{{ old('user_id','') }}">
                            </div>

                            <div class="form-group">
                                <label>在职状态</label>
                                <label>
                                    <input type="radio" name="status" value="1" checked /> 在职
                                </label>&nbsp;&nbsp;
                                <label>
                                    <input type="radio" name="status" value="2" /> 项目
                                </label>
                                <label>
                                    <input type="radio" name="status" value="3" /> 离职
                                </label>
                            </div>
                            <div class="form-group">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="audit_status" value="1" checked /> 已审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="audit_status" value="0" /> 待审核
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
        set_active_menu('manage_company',"{{ route('admin.company.data.people') }}");
    </script>
@endsection
