@extends('layouts.app')
@push('MyFontScripts')
    <script src="/plug/login/Scripts/register.js"></script>
@endpush
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">重置密码</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                        <div class="reg-box" id="verifyCheck" style="margin-top:20px;">
                    <form id="send_form" class="form-horizontal" method="POST" action="{{ route('sms.password.check') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="ticket" name="ticket">
                        <input type="hidden" id="randstr" name="randstr">
                        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">手机号码</label>

                            <div class="col-md-6">
                                <input id="phone" type="text" class="form-control required"
                                       name="phone" value="{{ old('phone') }}" required
                                       data-valid="isNonEmpty"
                                       data-error="手机号码不能为空"
                                />
                                @if (!$errors->has('phone'))
                                    <label class="focus">请填写11位有效的手机号码</label>
                                    <label class="focus valid"  style="color:darkred;"></label>
                                @else
                                    <label class="focus"><span></span></label>
                                    <label class="focus valid"  style="color:red;">{{ $errors->first('phone') }}</label>
                                @endif
                            </div>
                        </div>
                        <button style="display: none" type="button" class="btn btn-blue f-r3"
                                id="TencentCaptcha"
                                data-appid="{{env('TX_CAPTCHA_APPID')}}"
                                data-cbfn="captcha_callback"
                        >验证</button>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button id="btn_part1" type="button" class="btn btn-primary">
                                   发送重置短信验证码
                                </button>
                            </div>
                        </div>
                    </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('MyFontScripts')
    <script src="https://ssl.captcha.qq.com/TCaptcha.js"></script>
    <script>
        window.captcha_callback = function(res){
            // res（未通过验证）= {ret: 1, ticket: null}
            // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
            if(res.ret === 0){
                $("#randstr").val(res.randstr);
                $("#ticket").val(res.ticket);
                $("#btn_part1").attr("disabled","disabled");
                $("#TencentCaptcha").attr('id', 'no');
                $("#send_form").submit();
            }
        }
        //第一页的确定按钮
        $("#btn_part1").click(function(){
            if(!verifyCheck._click()) return;
            $("#TencentCaptcha").click();
        });
        function showoutc(){$(".m-sPopBg,.m-sPopCon").show();}
        function closeClause(){
            $(".m-sPopBg,.m-sPopCon").hide();
        }
    </script>
@endpush
