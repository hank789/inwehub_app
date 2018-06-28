@extends('admin/public/layout')

@section('title')
    浏览统计
@endsection

@section('content')
    <section class="content-header">
        <h1>
            浏览统计
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <div class="row">

                            <div class="col-xs-12">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.user.index') }}" method="GET">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-3 hidden-xs">
                                            <input type="text" name="date_range" id="date_range" class="form-control" placeholder="时间范围" value="{{ $filter['date_range'] or '' }}" />
                                        </div>
                                        <div class="col-xs-1">
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
                                        <th rowspan="2">时间</th>
                                        <th colspan="2">文章阅读数</th>
                                        <th colspan="2">精选阅读数</th>
                                        <th colspan="2">问题浏览数</th>
                                        <th colspan="2">回答浏览数</th>
                                        <th colspan="2">名片浏览数</th>
                                    </tr>
                                    <tr>
                                        <th>人数</th>
                                        <th>次数</th>
                                        <th>人数</th>
                                        <th>次数</th>
                                        <th>人数</th>
                                        <th>次数</th>
                                        <th>人数</th>
                                        <th>次数</th>
                                        <th>人数</th>
                                        <th>次数</th>
                                    </tr>
                                    @foreach($data as $i=>$item)
                                        <tr>
                                            <td>{{ $labelTimes[$i] }}</td>
                                            <td>{{ $item['article']['views'] }}</td>
                                            <td>{{ $item['article']['users'] }}</td>
                                            <td>{{ $item['recommend']['views'] }}</td>
                                            <td>{{ $item['recommend']['users'] }}</td>
                                            <td>{{ $item['question']['views'] }}</td>
                                            <td>{{ $item['question']['users'] }}</td>
                                            <td>{{ $item['answer']['views'] }}</td>
                                            <td>{{ $item['answer']['users'] }}</td>
                                            <td>{{ $item['resume']['views'] }}</td>
                                            <td>{{ $item['resume']['users'] }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        set_active_menu('manage_data',"{{ route('admin.data.views') }}");
    </script>
@endsection