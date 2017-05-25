@extends('h5::layout')

@section('title') 服务条款 @endsection


@section('content')

    {!! Setting()->get('about_us','') !!}

@endsection