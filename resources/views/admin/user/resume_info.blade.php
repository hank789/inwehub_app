@extends('admin/public/layout')

@section('title')
    简历信息
@endsection

@section('content')
    <section class="content-header">
        <h1>
            简历信息
            <small>用户简历信息</small>
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
                <div class="box box-default">
                    <form role="form" name="userForm" method="POST" action="{{ route('admin.user.store') }}">
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <div class="box-body">
                          @foreach($resumes as $item)
                          <div class="form-group">
                              <label>{{ $item->created_at }}</label>
                              <input type="file" name="avatar" />
                              <div style="margin-top: 10px;">
                                  <img src="{{ $item->getUrl() }}" />
                              </div>
                          </div>
                          @endforeach
                      </div>

                    </form>
                  </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_user',"{{ route('admin.user.index') }}");
    </script>
@endsection