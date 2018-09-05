@extends('layouts.app')
@section('title', '提示')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">提示</div>
        <div class="panel-body text-center">
            <h1>{{ $msg }}</h1>
            <a class="btn btn-primary" href="{{ route('login') }}">前往登录</a>
        </div>
    </div>
@endsection