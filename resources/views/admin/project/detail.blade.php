@extends('admin.public.layout')

@section('title')需求查看@endsection

@section('content')
    <section class="content-header">
        <h1>
            需求查看
            <small>需求查看</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{ $project->id }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>项目名称:</label>
                                <label>{{ $project->project_name }}</label>
                            </div>
                            <div class="form-group">
                                <label>项目类型:</label>
                                <label>{{ trans_project_type($project->project_type) }}</label>
                            </div>
                            <div class="form-group">
                                <label>项目阶段:</label>
                                <label>{{ trans_project_stage($project->project_stage) }}</label>
                            </div>
                            <div class="form-group">
                                <label>项目简介:</label>
                                <label>{{ $project->project_description }}</label>
                            </div>
                            <div class="form-group">
                                <label>项目状态:</label>
                                <label>{{ trans_company_apply_status($project->status) }}</label>
                            </div>

                            <div class="form-group">
                                <label>顾问数量:</label>
                                <label>{{ trans_project_worker_num($detail->worker_num) }}</label>
                            </div>

                            <div class="form-group">
                                <label>顾问级别:</label>
                                <label>{{ trans_project_worker_level($detail->worker_level) }}</label>
                            </div>

                            <div class="form-group">
                                <label>项目预算:</label>
                                <label>{{ $detail->project_amount }}万</label>
                            </div>

                            <div class="form-group">
                                <label>计费模式:</label>
                                <label>{{ trans_project_billing_mode($detail->billing_mode) }}</label>
                            </div>

                            <div class="form-group">
                                <label>项目开始时间:</label>
                                <label>{{ $detail->project_begin_time }}</label>
                            </div>

                            <div class="form-group">
                                <label>项目周期:</label>
                                <label>{{ trans_project_project_cycle($detail->project_cycle) }}</label>
                            </div>

                            <div class="form-group">
                                <label>工作密度:</label>
                                <label>{{ $detail->work_intensity }}</label>
                            </div>

                            <div class="form-group">
                                <label>是否接受远程工作:</label>
                                <label>{{ $detail->remote_work == 1 ? '接受':'不接受' }}</label>
                            </div>

                            <div class="form-group">
                                <label>差旅费用模式:</label>
                                <label>{{ trans_project_travel_expense($detail->travel_expense) }}</label>
                            </div>

                            <div class="form-group">
                                <label>工作地点:</label>
                                <label>{{ $detail->work_address }}</label>
                            </div>

                            <div class="form-group">
                                <label>企业名称:</label>
                                <label>{{ $detail->company_name }}</label>
                            </div>

                            <div class="form-group">
                                <label>企业简介:</label>
                                <label>{{ $detail->company_description }}</label>
                            </div>

                            <div class="form-group">
                                <label>对接人是否本人:</label>
                                <label>{{ $detail->company_represent_person_is_self ? '是':'否' }}</label>
                            </div>

                            <div class="form-group">
                                <label>对接人姓名:</label>
                                <label>{{ $detail->company_represent_person_name }}</label>
                            </div>

                            <div class="form-group">
                                <label>对接人职位:</label>
                                <label>{{ $detail->company_represent_person_title }}</label>
                            </div>

                            <div class="form-group">
                                <label>对接人手机:</label>
                                <label>{{ $detail->company_represent_person_phone }}</label>
                            </div>

                            <div class="form-group">
                                <label>对接人邮箱:</label>
                                <label>{{ $detail->company_represent_person_email }}</label>
                            </div>

                            <div class="form-group">
                                <label>发票抬头信息:</label>
                                <label>{{ $detail->company_billing_title }}</label>
                            </div>

                            <div class="form-group">
                                <label>开户银行:</label>
                                <label>{{ $detail->company_billing_bank }}</label>
                            </div>

                            <div class="form-group">
                                <label>开户账户:</label>
                                <label>{{ $detail->company_billing_account }}</label>
                            </div>

                            <div class="form-group">
                                <label>纳税识别号:</label>
                                <label>{{ $detail->company_billing_taxes }}</label>
                            </div>

                            <div class="form-group">
                                <label>认证资质:</label>
                                <label>{{ $detail->qualification_requirements }}</label>
                            </div>

                            <div class="form-group">
                                <label>其它资质:</label>
                                <label>{{ $detail->other_requirements }}</label>
                            </div>

                            <div class="form-group">
                                <label>是否需要查看顾问简历:</label>
                                <label>{{ $detail->is_view_resume ? '是':'否' }}</label>
                            </div>

                            <div class="form-group">
                                <label>是否需要顾问投递申请:</label>
                                <label>{{ $detail->is_apply_request ? '是':'否' }}</label>
                            </div>


                        </div>

                        <div class="box-footer">
                            <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('article_form','{{  route('admin.project.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                            <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过" onclick="confirm_submit('article_form','{{  route('admin.project.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
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