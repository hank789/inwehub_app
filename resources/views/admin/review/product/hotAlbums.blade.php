@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('title')
    热门专题
@endsection

@section('content')
    <section class="content-header">
        <h1>
            热门专题
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">

                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-stripped">
                                        <thead>
                                        <tr>
                                            <th>
                                                专题名
                                            </th>
                                            <th>
                                                标签描述
                                            </th>
                                            <th>
                                                排序
                                            </th>
                                            <th>
                                                操作
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($list as $key=>$album)
                                                <tr id="tr_idea_{{$key}}">
                                                    <td>
                                                        <select id="category_id_{{$key}}" name="category_id[]" class="form-control select_category">
                                                            @foreach(load_categories(['enterprise_review','product_album'],false,true) as $category)
                                                                <option value="{{ $category->id }}" @if($category->id == $album['category_id']) selected @endif>{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input name="desc" id="desc_{{$key}}" type="text" class="form-control" value="{{ $album['desc'] }}">
                                                    </td>

                                                    <td>
                                                        <input name="sort" id="sort_{{$key}}" type="text" class="form-control" value="{{ $album['sort'] }}">
                                                    </td>
                                                    <td>
                                                        @if ($album['category_id'] > 0)
                                                            <button class="btn btn-white" data-id="{{$album['id']}}" data-key="{{$key}}" onclick="deleteIdea(this)"><i class="fa fa-trash"></i> </button>
                                                        @endif
                                                            <button class="btn btn-white" data-id="{{$album['id']}}" data-key="{{$key}}" onclick="saveIdea(this)"><i class="fa fa-save"></i> </button>
                                                    </td>
                                                </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
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
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>

    <script type="text/javascript">
        $(function() {
            set_active_menu('manage_review', "{{ route('admin.review.album.hotIndex') }}");

            $('.select_category').select2();

        });
        function deleteIdea(obj) {
            if(!confirm('确认删除该记录？')){
                return false;
            }
            var id = $(obj).data('id');
            $.ajax({
                type: "post",
                data: {id: id},
                url:"{{route('admin.review.album.deleteHot')}}",
                success: function(data){
                    console.log(data);
                    window.location.reload();
                },
                error: function(data){
                    console.log(data);
                }
            });

        }

        function saveIdea(obj) {
            var id = $(obj).data('id');
            var key = $(obj).data('key');
            var formData = new FormData();
            formData.append('category_id',$('#category_id_'+key).val());
            formData.append('desc',$('#desc_'+key).val());
            formData.append('sort',$('#sort_'+key).val());
            formData.append('id',id);

            $.ajax({
                type: "post",
                data: formData,
                cache: false,
                processData: false, // 告诉jQuery不要去处理发送的数据
                contentType: false, // 告诉jQuery不要去设置Content-Type请求头
                url:"{{route('admin.review.album.saveHot')}}",
                success: function(data){
                    console.log(data);
                    $(obj).data('id',data.id);
                    alert('保存成功');
                    window.location.reload();
                },
                error: function(data){
                    console.log(data);
                }
            });
        }

    </script>
@endsection
