@extends('admin/public/layout')
@section('title')企业认证管理@endsection
@section('content')
    <section class="content-header">
        <h1>
            企业认证管理
            <small>管理认证企业</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="btn-group">
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="通过审核" onclick="confirm_submit('item_form','{{  route('admin.company.verify') }}','确认审核通过选中项？')"><i class="fa fa-check-square-o"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="审核不通过" onclick="confirm_submit('item_form','{{  route('admin.company.destroy') }}','确认禁用选中项？')"><i class="fa fa-lock"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.company.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-3">
                                            <select class="form-control" name="apply_status">
                                                <option value="-1">--状态--</option>
                                                @foreach(trans_company_apply_status('all') as $key => $status)
                                                    <option value="{{ $key }}" @if( isset($filter['apply_status']) && $filter['apply_status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xs-2 hidden-xs">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <button type="submit" class="btn btn-primary">搜索</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body  no-padding">
                        <form name="itemForm" id="item_form" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>UID</th>
                                        <th>企业</th>
                                        <th>行业领域</th>
                                        <th>公司规模</th>
                                        <th>统一社会信用代码</th>
                                        <th>开户银行</th>
                                        <th>开户账户</th>
                                        <th>公司地址</th>
                                        <th>公司电话</th>
                                        <th>对接人是否本人</th>
                                        <th>对接人姓名</th>
                                        <th>对接人职位</th>
                                        <th>对接人手机</th>
                                        <th>对接人邮箱</th>
                                        <th>验证模式</th>
                                        <th>状态</th>
                                        <th>更新时间</th>
                                    </tr>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $company->user_id }}"/></td>
                                            <td>{{ $company->user_id }}</td>
                                            <td>{{ $company->company_name }}</td>
                                            <td>{{ implode(',',array_column($company->tags()->toArray(),'name')) }}</td>
                                            <td>{{ $company->company_workers }}</td>
                                            <td>{{ $company->company_credit_code }}</td>
                                            <td>{{ $company->company_bank }}</td>
                                            <td>{{ $company->company_bank_account }}</td>
                                            <td>{{ $company->company_address }}</td>
                                            <td>{{ $company->company_work_phone }}</td>
                                            <td>{{ $company->company_represent_person_type ? '其他人':'申请者' }}</td>
                                            <td>{{ $company->company_represent_person_name }}</td>
                                            <td>{{ $company->company_represent_person_title }}</td>
                                            <td>{{ $company->company_represent_person_phone }}</td>
                                            <td>{{ $company->company_represent_person_email }}</td>
                                            <td>{{ $company->company_auth_mode }}</td>
                                            <td><span class="label @if($company->apply_status===0) label-warning  @elseif($company->apply_status===1) label-success @else label-default  @endif">{{ trans_company_apply_status($company->apply_status) }}</span> </td>
                                            <td>{{ timestamp_format($company->updated_at) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $companies->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $companies->render()) !!}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_company',"{{ route('admin.company.index') }}");
    </script>
@endsection