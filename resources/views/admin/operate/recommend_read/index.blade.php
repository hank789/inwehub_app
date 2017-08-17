@extends('admin/public/layout')

@section('title')阅读推荐管理@endsection

@section('content')
    <section class="content-header">
        <h1>阅读推荐管理</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <form role="form" name="listForm" method="post" action="{{ route('admin.operate.recommendRead.destroy',['id'=>0]) }}">
                        <input name="_method" type="hidden" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-header">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-default btn-sm" data-toggle="tooltip" title="删除推荐"><i class="fa fa-trash-o"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-body  no-padding">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th><input type="checkbox" class="checkbox-toggle"/></th>
                                        <th>排序</th>
                                        <th>标题</th>
                                        <th>封面图片</th>
                                        <th>频道</th>
                                        <th>状态</th>
                                        <th>更新时间</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($recommendations as $recommendation)
                                        <tr>
                                            <td><input type="checkbox" value="{{ $recommendation->id }}" name="ids[]"/></td>
                                            <td>{{ $recommendation->recommend_sort }}</td>
                                            <td>{{ $recommendation->title }}</td>
                                            <td><img width="100" height="100" src="{{ $recommendation->data['img'] ??'' }}"></td>
                                            <td>{{ $recommendation->category_name }}</td>
                                            <td><span class="label @if($recommendation->recommend_status===1) label-warning  @else label-success @endif">{{ trans_recommend_submission_status($recommendation->recommend_status) }}</span> </td>
                                            <td>{{ $recommendation->updated_at }}</td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    <a class="btn btn-default" href="{{ route('admin.operate.recommendRead.edit',['id'=>$recommendation->id]) }}" data-toggle="tooltip" title="编辑推荐信息"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="box-footer clearfix">
                            {!! str_replace('/?', '?', $recommendations->render()) !!}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('operations',"{{ route('admin.operate.recommendRead.index') }}");
    </script>
@endsection