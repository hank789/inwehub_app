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
                        <input type="hidden" name="id" value="{{ $demand->id }}">
                        <div class="box-body">
                            <div class="form-group">
                                <label>标题:</label>
                                <label>{{ $demand->title }}</label>
                            </div>
                            <div class="form-group">
                                <label>发布者:</label>
                                <label>{{ $demand->user->name }}</label>
                            </div>
                            <div class="form-group">
                                <label>发布者手机:</label>
                                <label>{{ $demand->user->mobile }}</label>
                            </div>
                            <div class="form-group">
                                <label>发布者公司:</label>
                                <label>{{ $demand->user->company }}</label>
                            </div>
                            <div class="form-group">
                                <label>发布者职位:</label>
                                <label>{{ $demand->user->title }}</label>
                            </div>
                            <div class="form-group">
                                <label>地址:</label>
                                <label>{{ $demand->address }}</label>
                            </div>
                            <div class="form-group">
                                <label>薪资:</label>
                                <label>{{ $demand->salary }}元/天</label>
                            </div>
                            <div class="form-group">
                                <label>行业:</label>
                                <label>{{ $demand->getIndustryName() }}</label>
                            </div>

                            <div class="form-group">
                                <label>项目开始时间:</label>
                                <label>{{ $demand->project_begin_time }}</label>
                            </div>

                            <div class="form-group">
                                <label>项目周期:</label>
                                <label>{{ trans_project_project_cycle($demand->project_cycle) }}</label>
                            </div>

                            <div class="form-group">
                                <label>需求描述:</label>
                                <label>{{ $demand->description }}</label>
                            </div>



                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_weapp_user',"{{ route('admin.weapp.demand.index') }}");
    </script>
@endsection