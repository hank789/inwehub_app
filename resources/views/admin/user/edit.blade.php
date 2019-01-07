@extends('admin/public/layout')

@section('css')
    <link href="{{ asset('/static/js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" />
    <link href="{{ asset('/static/js/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('/static/js/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet">
@endsection

@section('title')
    编辑用户
@endsection

@section('content')
    <section class="content-header">
        <h1>
            编辑用户
            <small>编辑用户信息</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">基本资料</h3>
                    </div>
                    <form role="form" name="userForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.user.update',['id'=>$user->id]) }}">
                        <input name="_method" type="hidden" value="PUT">
                        <input type="hidden" id="industry_tags" name="industry_tags" value="" />
                        <input type="hidden" id="skill_tags" name="skill_tags" value="" />


                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="box-body">
                          <div class="form-group @if ($errors->has('name')) has-error @endif">
                              <label for="name">用户姓名</label>
                              <input type="text" name="name" class="form-control " placeholder="姓名" value="{{ old('name',$user->name) }}">
                              @if ($errors->has('name')) <p class="help-block">{{ $errors->first('name') }}</p> @endif
                          </div>
                            <div class="form-group">
                                <label for="name">用户uuid</label>
                                <div>{{ $user->uuid }}</div>
                            </div>
                            <div class="form-group">
                                <label for="name">用户标签</label>
                                <div>
                                <ul class="taglist-inline ib">
                                    @foreach($user->userTags as $tagInfo)
                                        @if ($tagInfo->tag)
                                            <li class="tagPopup"><a class="tag" href="{{ route('ask.tag.index',['id'=>$tagInfo->tag->id]) }}">{{ $tagInfo->tag->name }}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>头像</label>
                                <input type="file" name="avatar" />
                                <div style="margin-top: 10px;">
                                    <img src="{{ $user->avatar }}" width="100"/>
                                </div>
                            </div>

                          <div class="form-group @if ($errors->has('email')) has-error @endif">
                              <label for="name">邮箱</label>
                              <input type="text" name="email" class="form-control " placeholder="邮箱地址" value="{{ old('email',$user->email) }}">
                              @if ($errors->has('email')) <p class="help-block">{{ $errors->first('email') }}</p> @endif
                          </div>
                            <div class="form-group @if ($errors->has('mobile')) has-error @endif">
                                <label for="name">手机</label>
                                <input type="text" name="mobile" class="form-control " placeholder="手机" value="{{ old('mobile',$user->mobile) }}">
                                @if ($errors->has('mobile')) <p class="help-block">{{ $errors->first('mobile') }}</p> @endif
                            </div>

                          <div class="form-group @if ($errors->has('password')) has-error @endif">
                              <label for="name">密码</label>
                              <input type="text" name="password" class="form-control " placeholder="密码" value="" />
                              @if ($errors->has('password')) <p class="help-block">{{ $errors->first('password') }}</p> @endif
                          </div>

                          <div class="form-group">
                              <label for="name">角色</label>
                              <select class="form-control" name="role_id">
                                    @foreach( $roles as $role )
                                        <option value="{{ $role->id }}" @if($user->getRoles()->contains($role->id)) selected @endif> {{ $role->name }}</option>
                                    @endforeach
                              </select>
                          </div>
                            <div class="form-group @if ($errors->has('rc_uid')) has-error @endif">
                                <label for="name">邀请者注册者id</label>
                                <input type="text" name="rc_uid" class="form-control " placeholder="邀请者id" value="{{ old('rc_uid',$user->rc_uid) }}">
                                @if ($errors->has('rc_uid')) <p class="help-block">{{ $errors->first('rc_uid') }}</p> @endif
                            </div>



                            <div class="form-group ">
                                <label for="time_friendly">性别</label>
                                <div class="radio">
                                    <label><input type="radio" name="gender" value="1" @if ( $user->gender === 1) checked @endif >男</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="gender" value="2" @if ( $user->gender === 2) checked @endif >女</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label><input type="radio" name="gender" value="0" @if ( $user->gender === 0) checked @endif >保密</label>
                                </div>
                            </div>

                          <div class="form-group @if ($errors->has('birthday')) has-error @endif">
                            <label for="name">出生日期</label>
                            <input type="text" name="birthday" class="form-control datepicker" placeholder="出生日期" value="{{ old('birthday',$user->birthday) }}" />
                            @if ($errors->has('birthday')) <p class="help-block">{{ $errors->first('birthday') }}</p> @endif
                          </div>

                            <div class="form-group">
                                <label for="setting-city" class="control-label">工作城市</label>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <select class="form-control" name="province" id="province">
                                            <option>请选择省份</option>
                                            @foreach($data['provinces'] as $key=>$province)
                                                <option value="{{ $key }}"  @if($user->province == $key) selected @endif>{{ $province }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-control" name="city" id="city">
                                            <option>请选择城市</option>
                                            @foreach($data['cities'] as $key => $city)
                                                <option value="{{ $key }}" @if($user->city == $key) selected @endif >{{ $city }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="setting-city" class="control-label">家乡城市</label>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <select class="form-control" name="hometown_province" id="hometown_province">
                                            <option>请选择省份</option>
                                            @foreach($data['provinces'] as $key=>$province)
                                                <option value="{{ $key }}"  @if($user->hometown_province == $key) selected @endif>{{ $province }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-control" name="hometown_city" id="hometown_city">
                                            <option>请选择城市</option>
                                            @foreach($data['hometown_cities'] as $key => $city)
                                                <option value="{{ $key }}" @if($user->hometown_city == $key) selected @endif >{{ $city }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('company')) has-error @endif">
                                <label for="company">当前公司</label>
                                <input type="text" name="company" class="form-control " placeholder="当前公司" value="{{ old('company',$user->company) }}">
                                @if ($errors->has('company')) <p class="help-block">{{ $errors->first('company') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->has('title')) has-error @endif">
                                <label for="title">身份职业</label>
                                <input type="text" name="title" class="form-control " placeholder="身份职业" value="{{ old('title',$user->title) }}">
                                @if ($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                            </div>

                            <div class="form-group @if ($errors->first('skill_tags')) has-error @endif">
                                <label for="select_skill_tags" class="control-label">擅长标签</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_skill_tags" name="select_skill_tags" class="form-control" multiple="multiple" >
                                            @foreach( $user->skillTags() as $tag)
                                                <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('skill_tags'))
                                            <span class="help-block">{{ $errors->first('skill_tags') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->first('industry_tags')) has-error @endif">
                                <label for="select_industry_tags" class="control-label">所在行业</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select id="select_industry_tags" name="select_industry_tags" class="form-control" multiple="multiple" >
                                            @foreach( $user->industryTags() as $tag)
                                                <option value="{{ $tag->id }}" selected>{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('industry_tags'))
                                            <span class="help-block">{{ $errors->first('industry_tags') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group @if ($errors->has('address_detail')) has-error @endif">
                                <label for="address_detail">详细地址</label>
                                <input type="text" name="address_detail" class="form-control " placeholder="详细地址" value="{{ old('address_detail',$user->address_detail) }}">
                                @if ($errors->has('address_detail')) <p class="help-block">{{ $errors->first('address_detail') }}</p> @endif
                            </div>


                            <div class="form-group @if ($errors->has('description')) has-error @endif">
                                <label for="name">自我介绍</label>
                                <textarea name="description" class="form-control " placeholder="自我介绍">{{ old('description',$user->description) }}</textarea>
                                @if ($errors->has('description')) <p class="help-block">{{ $errors->first('description') }}</p> @endif
                            </div>

                            <div class="form-group">
                                <label>状态</label>
                                <span class="text-muted">(禁用后用户将不能访问网站)</span>
                                <div class="radio">
                                    @foreach(trans_common_status('all') as $key => $status)
                                        <label>
                                            <input type="radio" name="status" value="{{ $key }}" @if($user->status === $key) checked @endif /> {{ $status }}
                                        </label>&nbsp;&nbsp;
                                    @endforeach
                                </div>
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
    <script src="{{ asset('/static/js/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('/static/js/bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js') }}"></script>
    <script src="{{ asset('/static/js/select2/js/select2.min.js')}}"></script>
    <script src="{{ asset('/static/js/autosize.min.js')}}"></script>

    <script type="text/javascript">
        $(function(){
            autosize($('textarea'));
            /*生日日历*/
            $(".datepicker").datepicker({
                format: "yyyy-mm-dd",
                language: "zh-CN",
                calendarWeeks: true,
                autoclose: true
            });
            /*加载省份城市*/
            $("#province").change(function(){
                var province_id = $(this).val();
                $("#city").load("{{ url('manager/ajax/loadCities') }}/"+province_id);
            });

            $("#hometown_province").change(function(){
                var province_id = $(this).val();
                $("#hometown_city").load("{{ url('manager/ajax/loadCities') }}/"+province_id);
            });

            $("#select_industry_tags").select2({
                theme:'bootstrap',
                placeholder: "所在行业",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 3
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

            $("#select_industry_tags").change(function(){
                $("#industry_tags").val($("#select_industry_tags").val());
            });


            $("#select_skill_tags").select2({
                theme:'bootstrap',
                placeholder: "擅长标签",
                ajax: {
                    url: '/manager/ajax/loadTags',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            word: params.term,
                            type: 5
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

            $("#select_skill_tags").change(function(){
                $("#skill_tags").val($("#select_skill_tags").val());
            });

            set_active_menu('manage_user',"{{ route('admin.user.index') }}");
        });
    </script>
@endsection