@extends('admin/public/layout')
@section('title')问题管理@endsection
@section('css')
    <style>
        .invite-question-modal{padding-top: 10px;}
        .invite-question-list{height: 275px;overflow: scroll;}
        .invite-question-item{height: 38px;margin-bottom: 20px;position: relative;}
        .invite-question-item img{float: left;width: 32px;border-radius: 50%;margin-right: 10px;}
        .invite-question-user-info{padding-right:70px;padding-left:40px;}
        .invite-question-user-desc{display: block;color: #999;font-size: 13px;}
        .invite-question-item-btn,.invite-question-item-btn_active{position: absolute;right: 15px;top: 10px;}
    </style>
@endsection
@section('content')
    <section class="content-header">
        <h1>
            问题管理
            <small>管理系统的所有问题</small>
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
                                    <a href="{{ route('ask.question.create') }}" target="_blank" class="btn btn-default btn-sm" data-toggle="tooltip" title="发起提问"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="设为精选" onclick="confirm_submit('item_form','{{  route('admin.question.verify_heart') }}','确认将选中项设为精选推荐项？')"><i class="fa fa-heart"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="设为推荐" onclick="confirm_submit('item_form','{{  route('admin.question.verify_recommend') }}','确认将选中项设为推荐项？')"><i class="fa fa-thumbs-o-up"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="取消推荐" onclick="confirm_submit('item_form','{{  route('admin.question.cancel_recommend') }}','确认将选中项取消推荐？')"><i class="fa fa-thumbs-o-down"></i></button>
                                    <button class="btn btn-default btn-sm" title="移动分类"  data-toggle="modal" data-target="#change_category_modal" ><i data-toggle="tooltip" title="移动分类" class="fa fa-bars" aria-hidden="true"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.question.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-xs-9">
                                <div class="row">
                                    <form name="searchForm" action="{{ route('admin.question.index') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="user_id" placeholder="UID" value="{{ $filter['user_id'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <input type="text" class="form-control" name="word" placeholder="关键词" value="{{ $filter['word'] or '' }}"/>
                                        </div>
                                        <div class="col-xs-2">
                                            <div>
                                                <label><input type="checkbox" name="is_recommend" value="1" @if ( $filter['is_recommend']??0) checked @endif >推荐</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                        <div class="col-xs-2">
                                            <select class="form-control" name="status">
                                                <option value="-1">不选择</option>
                                                @foreach(trans_question_status('all') as $key => $status)
                                                <option value="{{ $key }}" @if( isset($filter['status']) && $filter['status']==$key) selected @endif >{{ $status }}</option>
                                                @endforeach
                                            </select>
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
                                        <th><input type="checkbox" class="checkbox-toggle" /></th>
                                        <th>ID</th>
                                        <th>金额</th>
                                        <th>标签</th>
                                        <th>类型</th>
                                        <th style="width: 25%">标题</th>
                                        <th>提问人</th>
                                        <th>匿名</th>
                                        <th>拒绝/邀请</th>
                                        <th>回答</th>
                                        <th>承诺时间</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                    @foreach($questions as $question)
                                        <tr>
                                            <td><input type="checkbox" name="id[]" value="{{ $question->id }}"/></td>
                                            <td>{{ $question->id }}</td>
                                            <td><span class="text-gold"><i class="fa fa-database"></i> {{ $question->price }}</span></td>
                                            <td>@if( $question->category ) {{ $question->category->name }} | {{ implode(',',$question->tags->pluck('name')->toArray()) }} @else 无 @endif</td>
                                            <td>{{ $question->question_type == 1 ? '专业问答':'互动问答' }}</td>
                                            <td style="width: 25%"><a href="{{ route('ask.question.detail',['id'=>$question->id]) }}" target="_blank">{{ $question->title }}</a></td>
                                            <td>{{ $question->user->name }}<span class="text-muted">[UID:{{ $question->user_id }}]</span></td>
                                            <td>{{ $question->hide ? '匿名':'非匿名' }}</td>
                                            <td>{{ $question->invitations()->where('status',2)->count() }} / {{ $question->invitations()->count() }}</td>
                                            <td>{{ $question->answers }}</td>
                                            <td>
                                                @if ($answer = $question->answers()->where('adopted_at','>',0)->first())
                                                    {{ $answer->promise_time }}
                                                @endif
                                            </td>
                                            <td>{{ timestamp_format($question->created_at) }}</td>
                                            <td><span class="label @if($question->status===0) label-danger @elseif($question->status===1) label-warning @else label-success @endif">{{ trans_question_status($question->status).($question->is_recommend?' | 已推荐':'').($question->is_hot?' | 热门':'') }}</span> </td>
                                            <td>
                                                <div class="btn-group-xs" >
                                                    @if( !in_array($question->status, [4,6,7]) )
                                                        <a class="btn btn-default" href="#" onclick="showInviteModal(this)" data-qid="{{ $question->id }}" data-qcontent="{{ $question->title }}" data-toggle="tooltip" title="邀请专家"><i class="fa fa-envelope"></i></a>
                                                    @endif
                                                    <a class="btn btn-default" target="_blank" href="{{ route('ask.question.detail',['id'=>$question->id]) }}" data-toggle="tooltip" title="查看"><i class="fa fa-eye"></i></a>
                                                    <a class="btn btn-default" target="_blank" href="{{ route('ask.question.edit',['id'=>$question->id]) }}" data-toggle="tooltip" title="编辑"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="btn-group">
                                    <a href="{{ route('ask.question.create') }}" target="_blank" class="btn btn-default btn-sm" data-toggle="tooltip" title="发起提问"><i class="fa fa-plus"></i></a>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="设为推荐" onclick="confirm_submit('item_form','{{  route('admin.question.verify_recommend') }}','确认将选中项设为推荐项？')"><i class="fa fa-thumbs-o-up"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="取消推荐" onclick="confirm_submit('item_form','{{  route('admin.question.cancel_recommend') }}','确认将选中项取消推荐？')"><i class="fa fa-thumbs-o-down"></i></button>
                                    <button class="btn btn-default btn-sm" title="移动分类"  data-toggle="modal" data-target="#change_category_modal" ><i data-toggle="tooltip" title="移动分类" class="fa fa-bars" aria-hidden="true"></i></button>
                                    <button class="btn btn-default btn-sm" data-toggle="tooltip" title="删除选中项" onclick="confirm_submit('item_form','{{  route('admin.question.destroy') }}','确认删除选中项？')"><i class="fa fa-trash-o"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-right">
                                    <span class="total-num">共 {{ $questions->total() }} 条数据</span>
                                    {!! str_replace('/?', '?', $questions->appends($filter)->render()) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal" id="inviteAnswer" tabindex="-1" role="dialog" aria-labelledby="inviteAnswerLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="appendModalLabel">邀请回答</h4>
                        <input type='hidden' id='question_id' name='question_id' value=''>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success" role="alert" id="rewardAlert">
                            <i class="fa fa-exclamation-circle"></i> <span id="question_description"></span>
                        </div>
                        <form class="invite-popup" id="inviteEmailForm"  action="#" method="get">
                            <div style="position: relative;">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-by="username" href="#by-username" data-toggle="tab">站内邀请</a></li>
                                </ul>
                                <div class="tab-content invite-tab-content mt-10">
                                    <div class="tab-pane active" id="by-username" data-type="username">
                                        <div class="search-user" id="questionSlug">
                                            <input id="invite_word" class="text-28 form-control" type="text" name="word" autocomplete="off" placeholder="搜索你要邀请的人">
                                        </div>
                                        <p class="help-block" id="questionInviteUsers"></p>
                                        <div class="invite-question-modal">
                                            <div class="row invite-question-list" id="invite_user_list">
                                                <div class="text-center" id="invite_loading">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="by-email" data-type="email">
                                        <div class="mb-10">
                                            <input class="text-28 form-control" type="email" name="sendTo" placeholder="Email 地址">
                                        </div>
                                        <p><textarea class="textarea-13 form-control" name="message" rows="5">我在 {{ Setting()->get('website_name') }} 上遇到了问题，希望您能帮我解答 </textarea></p>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="modal-footer" style="display:none;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary invite-email-btn">确认</button>
                    </div>
                </div>
            </div>
        </div>

    </section>

@endsection

@section('script')
    @include("admin.public.change_category_modal",['type'=>'questions','form_id'=>'item_form','form_action'=>route('admin.question.changeCategories')])
    <script type="text/javascript">
        set_active_menu('manage_content',"{{ route('admin.question.index') }}");
        var invitation_timer = null;
        $(document).ready(function() {
            /*邀请回答模块逻辑处理*/
            /*私信模块处理*/

            $('#inviteAnswer').on('show.bs.modal', function (event) {

                var button = $(event.relatedTarget);
                var modal = $(this);
                loadInviteUsers($("#question_id").val(),'');
                loadQuestionInvitedUsers($("#question_id").val(),'part');

            });


            $("#invite_word").on("keydown",function(){
                if(invitation_timer){
                    clearTimeout(invitation_timer);
                }
                invitation_timer = setTimeout(function() {
                    var word = $("#invite_word").val();
                    console.log(word);
                    loadInviteUsers($("#question_id").val(),word);
                }, 500);
            });

            $(".invite-question-list").on("click",".invite-question-item-btn",function(){
                var invite_btn = $(this);
                var question_id = invite_btn.data('question_id');
                var user_id = invite_btn.data('user_id');

                $.ajax({
                    type: "get",
                    url:"/manager/question/invite/"+question_id+"/"+user_id,
                    success: function(data){
                        if(data.code > 0){
                            alert(data.message);
                            return false;
                        }
                        invite_btn.html('已邀请');
                        invite_btn.attr("class","btn btn-default btn-xs invite-question-item-btn disabled");
                        loadQuestionInvitedUsers(question_id,'part');
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });

            $("#inviteAnswer").on("click","#showAllInvitedUsers",function(){
                loadQuestionInvitedUsers($("#question_id").val(),'all');
            });

            /*tag切换*/
            $('#inviteAnswer a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var tabBy = $(this).data("by");
                if( tabBy == 'email' ){
                    $("#inviteAnswer .modal-footer").show();
                }else{
                    $("#inviteAnswer .modal-footer").hide();
                }

            });

        });

        /**
         * @param questionId
         * @param word
         */
        function loadInviteUsers(questionId,word){
            $.ajax({
                type: "get",
                url: "/manager/ajax/loadInviteUsers",
                data:{question_id:questionId,word:word},
                success: function(data){
                    console.log(data);
                    var inviteItemHtml = '';
                    if(data.code > 0){
                        inviteItemHtml = '<div class="text-center" id="invite_loading"><p>暂无数据</p></div>';
                    }else{
                        $.each(data.message,function(i,item){
                            inviteItemHtml+= '<div class="col-md-12 invite-question-item">' +
                                    '<img src="'+item.avatar+'" />'+
                                    '<div class="invite-question-user-info">'+
                                    '<a class="invite-question-user-name" target="_blank" href="'+item.url+'">'+item.name+'</a>'+
                                    (item.isExpert ? '<i class="fa fa-graduation-cap" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="" data-original-title="已通过行家认证"></i>':'') +
                                    '<span class="invite-question-user-desc">'+item.tag_name+' 标签下有 '+item.tag_answers+' 个回答</span>'+
                                    '</div>';
                            if(item.isInvited>0){
                                inviteItemHtml += '<button type="button" class="btn btn-default btn-xs invite-question-item-btn disabled" data-question_id="'+questionId+'"  data-user_id="'+item.id+'">已邀请</button>';
                            }else{
                                inviteItemHtml += '<button type="button" class="btn btn-default btn-xs invite-question-item-btn" data-question_id="'+questionId+'"  data-user_id="'+item.id+'">邀请回答</button>';
                            }
                            inviteItemHtml += '</div>';
                        });
                    }
                    $("#invite_user_list").html(inviteItemHtml);
                },
                error: function(data){
                    console.log(data);
                    $("#invite_user_list").html('<div class="text-center" id="invite_loading"><p>操作出错</p></div>');

                }
            });
        }

        /*加载已被邀请的用户信息*/
        function loadQuestionInvitedUsers(questionId,type){
            $("#questionInviteUsers").load('/manager/question/'+questionId+'/invitations/'+type);
        }

        function showInviteModal(obj){
            $('#question_id').val($(obj).data('qid'));
            $('#question_description').html($(obj).data('qcontent'));
            $('#inviteAnswer').modal('show');
        }

    </script>
@endsection