@extends('layouts.app')
@push('MyFontStyle')
<link href="/plug/login/Css/gloab.css" rel="stylesheet">
<link href="/plug/login/Css/index.css" rel="stylesheet">
@endpush
@push('MyFontScripts')
    <script src="/plug/login/Scripts/register.js"></script>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="bgf4">
            <div class="login-box f-mt10">
                <div class="main bgf">
                    <div class="reg-box-pan display-inline">
                        <div class="step">
                            <ul>
                                <li class="col-xs-4 on">
                                    <span class="num"><em class="f-r5"></em><i>1</i></span>
                                    <span class="line_bg lbg-r"></span>
                                    <p class="lbg-txt">填写手机号码</p>
                                </li>
                                <li class="col-xs-4 on">
                                    <span class="num"><em class="f-r5"></em><i>2</i></span>
                                    <span class="line_bg lbg-l"></span>
                                    <span class="line_bg lbg-r"></span>
                                    <p class="lbg-txt">验证账户信息</p>
                                </li>
                                <li class="col-xs-4">
                                    <span class="num"><em class="f-r5"></em><i>3</i></span>
                                    <span class="line_bg lbg-l"></span>
                                    <p class="lbg-txt">注册成功</p>
                                </li>
                            </ul>
                        </div>
                        <form id="part1form" method="POST" action="{{ route('register3') }}">
                            {{csrf_field()}}
                          <input type="hidden" name="key" value="{{$key}}">
                        <div class="reg-box" id="verifyCheck" style="margin-top:20px;">
                            <div class="part1">
                                <div class="alert alert-info" style="width:700px">短信已发送至您手机，请输入短信中的验证码，确保您的手机号真实有效。</div>
                                <div class="item col-xs-12 f-mb10" style="height:auto">
                                    <span class="intelligent-label f-fl">手机号：</span>
                                    <div class="f-fl item-ifo c-blue">
                                        {{$phone}}
                                    </div>
                                </div>
                                <div class="item col-xs-12">
                                    <span class="intelligent-label f-fl"><b class="ftx04">*</b>验证码：</span>
                                    <div class="f-fl item-ifo">
                                        <input name="code" type="text" value="{{old('code')}}" required
                                             class="txt03 f-r3 f-fl required" style="width:167px"/>
                                        <span class="btn btn-gray f-r3 f-ml5 f-size13" id="time_box" disabled style="width:97px;display:none;">发送验证码</span>
                                        <span class="ie8 icon-close close hide" style="right:130px"></span>
                                        <label class="icon-sucessfill blank hide"></label>
                                        @if (!$errors->has('code'))
                                        <label class="focus"><span>请查收手机短信，并填写短信中的验证码（此验证码3分钟内有效）</span></label>
                                        <label class="focus valid"></label>
                                        @else
                                            <label class="focus"><span></span></label>
                                            <label class="focus valid">{{ $errors->first('code') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="item col-xs-12">
                                    <span class="intelligent-label f-fl"><b class="ftx04">*</b>用户名：</span>
                                    <div class="f-fl item-ifo">
                                        <input type="text" name="name" value="{{old('name')}}" required class="txt03 f-r3 required"/>
                                        <span class="ie8 icon-close close hide"></span>
                                        <label class="icon-sucessfill blank hide"></label>
                                        @if (!$errors->has('name'))
                                            <label class="focus"><span>请填写长度2-8位的用户名</span></label>
                                            <label class="focus valid"></label>
                                        @else
                                            <label class="focus"><span></span></label>
                                            <label class="focus valid">{{ $errors->first('name') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="item col-xs-12">
                                    <span class="intelligent-label f-fl"><b class="ftx04">*</b>密码：</span>
                                    <div class="f-fl item-ifo">
                                        <input type="password" name="password" value="{{old('password')}}" class="txt03 f-r3 required" required/>
                                        <span class="ie8 icon-close close hide" style="right:55px"></span>
                                        <span class="showpwd" data-eye="password"></span>
                                        <label class="icon-sucessfill blank hide"></label>
                                        @if (!$errors->has('password'))
                                            <label class="focus">6-20位英文（区分大小写）、数字、字符的组合</label>
                                            <label class="focus valid"></label>
                                        @else
                                            <label class="focus"><span></span></label>
                                            <label class="focus valid">{{ $errors->first('password') }}</label>
                                        @endif
                                        <span class="clearfix"></span>
                                        <label class="strength">
                                            <span class="f-fl f-size12">安全程度：</span>
                                            <b><i>弱</i><i>中</i><i>强</i></b>
                                        </label>
                                    </div>
                                </div>
                                <div class="item col-xs-12">
                                    <span class="intelligent-label f-fl"><b class="ftx04">*</b>确认密码：</span>
                                    <div class="f-fl item-ifo">
                                        <input type="password" name="password_confirmation" value="{{old('password_confirmation')}}" required
                                               class="txt03 f-r3 required"/>
                                        <span class="ie8 icon-close close hide" style="right:55px"></span>
                                        <span class="showpwd" data-eye="rePassword"></span>
                                        <label class="icon-sucessfill blank hide"></label>
                                        <label class="focus">请再输入一遍上面的密码</label>
                                        <label class="focus valid"></label>
                                    </div>
                                </div>

                                <div class="item col-xs-12">
                                    <span class="intelligent-label f-fl">&nbsp;</span>
                                    <div class="f-fl item-ifo">
                                        <button class="btn btn-blue f-r3" id="btn_part2">下一步</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </form>
        </div>
    </div>
</div>
@endsection
