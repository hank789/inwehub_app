@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/css/fileinput/fileinput.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/css/fileinput/theme.css')}}" rel="stylesheet">
@endsection

@section('title')
    编辑产品
@endsection

@section('content')
    <section class="content-header">
        <h1>
            编辑产品
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_base" data-toggle="tab" aria-expanded="false">产品编辑</a></li>
                        <li><a href="#tab_news" data-toggle="tab" aria-expanded="true">产品亮点</a></li>
                        <li><a href="#tab_idea" data-toggle="tab" aria-expanded="true">专家观点</a></li>
                        <li><a href="#tab_case" data-toggle="tab" aria-expanded="true">案例展示</a></li>
                        <li><a href="#tab_gzh" data-toggle="tab" aria-expanded="true">资讯管理</a></li>
                        <li><a href="#tab_rel" data-toggle="tab" aria-expanded="true">相关推荐</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_base">
                            <div class="box box-default">
                    <form role="form" name="tagForm" id="tag_form" method="POST" enctype="multipart/form-data" action="{{ route('admin.review.product.update',['id'=>$tag->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" name="_token" id="editor_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                            <div class="form-group @if ($errors->has('name')) has-error @endif">
                                <label for="name">产品名称</label>
                                <input type="text" name="name" class="form-control " placeholder="标签名称" value="{{ old('name',$tag->tag->name) }}">
                                @if ($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>产品图标</label>
                                <input type="file" accept="image/*" name="logo" />
                                @if($tag->tag->logo)
                                <div style="margin-top: 10px;">
                                    <img style="width: 150px;height: 150px;" src="{{ $tag->tag->logo }}" />
                                </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>产品介绍图</label>
                                <input onchange="uploadAndPreviewImg(1,'cover_pic','ccc')" type="file" id="cover_pic" accept="image/*" name="cover_pic" />
                                <fieldset style="width:500px;">
                                    <div style="position: relative;" id="ccc">
                                    </div>
                                </fieldset>
                                @if($tag->tag->getCoverPic())
                                    <div style="margin-top: 10px;">
                                        <img style="width: 150px;height: 150px;" src="{{ $tag->tag->getCoverPic() }}" />
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>分类</label>
                                <select id="category_id" name="category_id[]" class="form-control" multiple="multiple" >
                                    @foreach(load_categories(['enterprise_review','product_album'],false,true) as $category)
                                        <option value="{{ $category->id }}" @if($category->id == $tag->category_id) selected @endif>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group @if ($errors->has('summary')) has-error @endif">
                                <label for="name">简介(供前台展示)</label>
                                <textarea name="summary" class="form-control" placeholder="简介" style="height: 280px;">{{ old('summary',$tag->tag->summary) }}</textarea>
                                @if ($errors->has('summary')) <p class="help-block">{{ $errors->first('summary') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('name')) has-error @endif">
                                <label for="name">亮点说明</label>
                                <input type="text" name="advance_desc" class="form-control " placeholder="亮点说明" value="{{ old('advance_desc',$tag->tag->getAdvanceDesc()) }}">
                                @if ($errors->has('advance_desc')) <p class="help-block">{{ $errors->first('advance_desc') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">关键词(供搜索用,多个以逗号隔开)</label>
                                <textarea name="description" class="form-control" placeholder="关键词" style="height: 80px;">{{ old('description',$tag->tag->getKeywords()) }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('is_pro')) has-error @endif">
                                <label>专业版</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="is_pro" value="0" @if($tag->tag->is_pro==0) checked @endif /> 否
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="is_pro" value="1" @if($tag->tag->is_pro==1) checked @endif /> 是
                                    </label>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('author_id')) has-error @endif">
                                <label for="author_id_select" class="control-label">管理者</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="author_id_select" name="author_id_select[]" multiple="multiple" class="form-control">
                                            @foreach($managers as $manager)
                                                <option value="{{ $manager->user->id }}" selected> {{ '<span><img style="width: 30px;height: 20px;" src="' .($manager->user->avatar) .'" class="img-flag" />' . ($manager->user->name).'</span>'}} </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('author_id'))
                                            <span class="help-block">{{ $errors->first('author_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('status')) has-error @endif">
                                <label>审核状态</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="status" value="0" @if($tag->status==0) checked @endif /> 待审核
                                    </label>&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" name="status" value="1" @if($tag->status==1) checked @endif /> 已审核
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary editor-submit" >保存</button>
                            <button type="reset" class="btn btn-success">重置</button>
                        </div>
                    </form>
                </div>
                        </div>
                        <div class="tab-pane" id="tab_news">
                                <div class="box-body">
                                    <div class="file-loading">
                                        <label>Preview File Icon</label>
                                        <input id="introduce_pic" name="introduce_pic[]" type="file" multiple accept="image/*">
                                    </div>

                                </div>
                        </div>
                        <div class="tab-pane" id="tab_idea">
                            <div class="panel-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-stripped">
                                        <thead>
                                        <tr>
                                            <th>
                                                专家头像
                                            </th>
                                            <th>
                                                专家姓名
                                            </th>
                                            <th>
                                                头衔
                                            </th>
                                            <th>
                                                观点
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
                                        @foreach($ideaList as $key=>$idea)
                                                <tr id="tr_idea_{{$key}}">
                                                    <td>
                                                        <input onchange="uploadAndPreviewImg(1,'avatar_{{$key}}','avatar_preview_{{$key}}')" type="file" id="avatar_{{$key}}" accept="image/*" name="avatar" />
                                                        <fieldset style="width:300px;">
                                                            <div style="position: relative;" id="avatar_preview_{{$key}}">
                                                            </div>
                                                        </fieldset>
                                                        @if ($idea['avatar'])
                                                            <img style="width: 150px;height: 150px;" src="{{$idea['avatar']}}">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input name="name" id="name_{{$key}}" type="text" class="form-control" value="{{ $idea['name'] }}">
                                                    </td>
                                                    <td>
                                                        <input name="title" id="title_{{$key}}" type="text" class="form-control" value="{{ $idea['title'] }}">
                                                    </td>
                                                    <td>
                                                        <textarea name="content" id="content_{{$key}}" class="form-control" placeholder="观点" style="height: 80px;">{{ $idea['content'] }}</textarea>
                                                    </td>
                                                    <td>
                                                        <input name="sort" id="sort_{{$key}}" type="text" class="form-control" value="{{ $idea['sort'] }}">
                                                    </td>
                                                    <td>
                                                        @if ($idea['id'] > 0)
                                                            <button class="btn btn-white" data-id="{{$idea['id']}}" data-key="{{$key}}" onclick="deleteIdea(this)"><i class="fa fa-trash"></i> </button>
                                                        @endif
                                                            <button class="btn btn-white" data-tag_id="{{$tag->tag_id}}" data-id="{{$idea['id']}}" data-key="{{$key}}" onclick="saveIdea(this)"><i class="fa fa-save"></i> </button>
                                                    </td>
                                                </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <div class="tab-pane" id="tab_case">

                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="topic_news_table" style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>标题</th>
                                            <th>封面图</th>
                                            <th>类型</th>
                                            <th>排序</th>
                                            <th>简介</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($caseList as $case)
                                            <tr id="case_{{$case->id}}">
                                                <td><a href="{{ $case->content['link_url'] }}" target="_blank">{{ $case->content['title'] }}</a></td>
                                                <td>
                                                    <img style="width: 150px;height: 150px;" src="{{ $case->content['cover_pic'] }}" />
                                                </td>
                                                <td>{{ $case->content['type'] }}</td>
                                                <td>{{ $case->sort }}</td>
                                                <td>{{ $case->content['desc'] }}</td>
                                                <td><span class="label @if($case->status===0) label-warning  @else label-success @endif">{{ trans_common_status($case->status) }}</span></td>
                                                <td>
                                                    <div class="btn-group-xs" >
                                                        <a class="btn btn-default" target="_blank" href="{{ route('admin.review.product.editCase',['id'=>$case->id]) }}" data-toggle="tooltip" title="修改"><i class="fa fa-edit"></i></a>
                                                        <button class="btn btn-warning" onclick="deleteCase(this)" data-id="{{$case->id}}" data-toggle="tooltip" title="删除"><i class="fa fa-trash"></i></button>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer">
                                <a class="btn btn-primary" href="{{ route('admin.review.product.addCase',['tag_id'=>$tag->tag_id]) }}" target="_blank" data-toggle="tooltip" title="添加案例">添加案例</a>
                            </div>
                        </div>

                        <div class="tab-pane" id="tab_gzh">

                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="topic_news_table" style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>公众号名称</th>
                                            <th>微信号</th>
                                            <th>最后抓取时间</th>
                                            <th>今日抓取文章数</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($gzhList as $gzh)
                                            @php
                                            $mpInfo = \App\Models\Scraper\WechatMpInfo::find($gzh->content['mp_id'])
                                            @endphp
                                            <tr id="gzh_{{$gzh->id}}">
                                                <td>{{ $mpInfo->name }}</td>
                                                <td>{{ $gzh->content['wx_hao'] }}</td>
                                                <td>{{ $mpInfo->update_time }}</td>
                                                <td>{{ $mpInfo->countTodayArticle() }}</td>
                                                <td><span class="label @if($gzh->status===0) label-warning  @else label-success @endif">{{ trans_common_status($gzh->status) }}</span></td>
                                                <td>
                                                    <div class="btn-group-xs" >
                                                        <button class="btn btn-warning" onclick="deleteGzh(this)" data-id="{{$gzh->id}}" data-toggle="tooltip" title="删除"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer">
                                <a class="btn btn-primary" href="{{ route('admin.review.product.newsList',['tag_id'=>$tag->tag_id]) }}" target="_blank" data-toggle="tooltip" title="查看资讯列表">查看资讯列表</a>
                                <a class="btn btn-primary" href="{{ route('admin.review.product.addGzh',['tag_id'=>$tag->tag_id]) }}" target="_blank" data-toggle="tooltip" title="添加公众号">添加公众号</a>
                                <a class="btn btn-primary" href="{{ route('admin.review.product.addNews',['tag_id'=>$tag->tag_id]) }}" target="_blank" data-toggle="tooltip" title="添加资讯">添加资讯</a>
                            </div>
                        </div>

                        <div class="tab-pane" id="tab_rel">
                            <div class="box box-default">


                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>只显示人工推荐</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="only_show_relate_products" value="0" @if($only_show_relate_products==0) checked @endif /> 否
                                                </label>&nbsp;&nbsp;
                                                <label>
                                                    <input type="radio" name="only_show_relate_products" value="1" @if($only_show_relate_products==1) checked @endif /> 是
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>选择相关产品</label>
                                            <select id="rel_product_id" name="rel_product_id[]" class="form-control" multiple="multiple" style="width: 100%;" >
                                                @foreach( $rel_tags as $rel_tag)
                                                    <option value="{{ $rel_tag->id }}" selected>{{ $rel_tag->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button type="button" data-id="{{$tag->tag_id}}" class="btn btn-primary editor-submit" onclick="updateRelTags(this)">保存</button>
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
    <script src="{{ asset('/static/js/fileinput/piexif.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/purify.min.js')}}"></script>
    <script src="{{ asset('/static/js/sortable.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/fileinput.min.js')}}"></script>
    <script src="{{ asset('/static/js/fileinput/theme.js')}}"></script>

    <script type="text/javascript">
        $(function() {
            set_active_menu('manage_review', "{{ route('admin.review.product.index') }}");
            $('#category_id').select2();

            $("#author_id_select").select2({
                theme:'bootstrap',
                placeholder: "管理人员",
                templateResult: function(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    return $('<span><img style="width: 30px;height: 20px;" src="' + state.avatar + '" class="img-flag" /> ' + state.name + '</span>');
                },
                templateSelection: function (state) {
                    console.log(state.text);
                    if (!state.id) return state.text; // optgroup
                    if (state.text) return $(state.text);
                    return $('<span><img style="width: 30px;height: 20px;" src="' + state.avatar + '" class="img-flag" /> ' + (state.name || state.text) + '</span>');
                },
                ajax: {
                    url: '/manager/ajax/loadUsers',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength:1,
                tags:false
            });

            $("#rel_product_id").select2({
                theme:'bootstrap',
                placeholder: "相关产品",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 7
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength:2,
                tags:false
            });

            /*
            $(".file").on('fileselect', function(event, n, l) {
            alert('File Selected. Name: ' + l + ', Num: ' + n);
            });
            */
            $("#introduce_pic").fileinput({
                theme: 'fas',
                uploadUrl: "{{route('admin.review.product.updateIntroducePic',['id'=>$tag->tag_id])}}",
                showUpload: true,
                uploadAsync: false,
                maxFileCount: 10,
                showCaption: false,
                allowedFileExtensions: ['jpg', 'png','jpeg', 'gif'],
                overwriteInitial: false,
                initialPreviewAsData: true,
                initialPreview: {!! $initialPreview !!},
                initialPreviewConfig: {!! $initialPreviewConfig !!}
            });
            $('#introduce_pic').on('filesorted', function(event, params) {
                console.log('File sorted previewId:'+ params.previewId + ';oldIndex:' +params.oldIndex + ';newIndex'+params.newIndex);
                console.log(params.stack);
                $.ajax({
                    type: "post",
                    data: {newList: params.stack},
                    url:"{{route('admin.review.product.sortIntroducePic',['id'=>$tag->tag_id])}}",
                    success: function(data){
                        console.log(data);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });
        });
        function deleteCase(obj) {
            if(!confirm('确认删除该记录？')){
                return false;
            }
            var id = $(obj).data('id');
            $.ajax({
                type: "post",
                data: {id: id},
                url:"{{route('admin.review.product.deleteCase')}}",
                success: function(data){
                    console.log(data);
                    $("#case_" + id).css('display','none');
                },
                error: function(data){
                    console.log(data);
                }
            });

        }
        function deleteGzh(obj) {
            if(!confirm('确认删除该记录？')){
                return false;
            }
            var id = $(obj).data('id');
            $.ajax({
                type: "post",
                data: {id: id},
                url:"{{route('admin.review.product.deleteGzh')}}",
                success: function(data){
                    console.log(data);
                    $("#gzh_" + id).css('display','none');
                },
                error: function(data){
                    console.log(data);
                }
            });
        }

        function deleteIdea(obj) {
            if(!confirm('确认删除该记录？')){
                return false;
            }
            var id = $(obj).data('id');
            var key = $(obj).data('key');
            $.ajax({
                type: "post",
                data: {id: id},
                url:"{{route('admin.review.product.deleteIdea')}}",
                success: function(data){
                    console.log(data);
                    $("#tr_idea_" + key).css('display','none');
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
            formData.append('file', $('#avatar_'+key)[0].files[0]);  //添加图片信息的参数
            formData.append('name',$('#name_'+key).val());
            formData.append('title',$('#title_'+key).val());
            formData.append('content',$('#content_'+key).val());
            formData.append('sort',$('#sort_'+key).val());
            formData.append('id',id);

            $.ajax({
                type: "post",
                data: formData,
                cache: false,
                processData: false, // 告诉jQuery不要去处理发送的数据
                contentType: false, // 告诉jQuery不要去设置Content-Type请求头
                url:"{{route('admin.review.product.saveIdea',['tag_id'=>$tag->tag_id])}}",
                success: function(data){
                    console.log(data);
                    $(obj).data('id',data.id);
                    alert('保存成功');
                },
                error: function(data){
                    console.log(data);
                }
            });
        }

        function updateRelTags(obj) {
            var id = $(obj).data('id');
            var tags = $('#rel_product_id').val();
            var showType = $('input:radio[name=only_show_relate_products]:checked').val();
            $.ajax({
                type: "post",
                data: {rel_tags: tags, isOnlyShow: showType},
                url:"{{route('admin.review.product.relateProducts',['tag_id'=>$tag->tag_id])}}",
                success: function(data){
                    console.log(data);
                    alert('保存成功');
                },
                error: function(data){
                    console.log(data);
                }
            });
        }
    </script>
@endsection
