@extends('admin/public/layout')

@section('title')添加产品资讯@endsection

@section('content')
    <section class="content-header">
        <h1>
            添加产品资讯-{{$tag->name}}
            <small>目前只支持微信公众号的链接</small>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <form id="article_form" method="POST" role="form" enctype="multipart/form-data" action="{{ route('admin.review.product.storeNews',['tag_id'=>$tag->id]) }}">
                        <input type="hidden" id="editor_token" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="url_previous" value="{{ url()->previous() }}">

                        <div class="box-body">
                            <div class="form-group @if($errors->has('link_url')) has-error @endif ">
                                <label for="link_url">微信公众号文章链接地址:</label>
                                <input id="link_url" type="text" name="link_url"  class="form-control input-lg" placeholder="" value="{{ old('link_url','') }}" />
                                @if($errors->has('link_url')) <p class="help-block">{{ $errors->first('link_url') }}</p> @endif
                            </div>

                        </div>

                        <div class="box-footer">
                                <button type="submit" class="btn btn-primary editor-submit">添加资讯</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('script')
    <script type="text/javascript">
        set_active_menu('manage_review',"{{ route('admin.review.product.index') }}");
    </script>
@endsection