@extends('layouts.app')
@section('title', $product->title)
@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body product-info">
                    <div class="row">
                        <div class="col-sm-5">
                            <img class="cover" src="{{ $product->full_image }}" alt="">
                        </div>
                        <div class="col-sm-7">
                            <div class="title">{{ $product->long_title }}</div>
                            <!-- 众筹商品模块开始 -->
                            @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                <div class="crowdfunding-info">
                                    <div class="price"><label  style="color: #2ab27b;font-size: 16px;">众筹价:</label><em>￥</em><span id="sku_price">{{ $product->price }}</span></div>
                                    <div class="total-amount"><span class="symbol">已筹到￥</span>{{ $product->crowdfunding->total_amount }}</div>
                                    <!-- 这里使用了 Bootstrap 的进度条组件 -->
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped"
                                             role="progressbar"
                                             aria-valuenow="{{ $product->crowdfunding->percent }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="min-width: 1em; width: {{ min($product->crowdfunding->percent, 100) }}%">
                                        </div>
                                    </div>
                                    <div class="progress-info">
                                        <span class="current-progress">当前进度：{{ $product->crowdfunding->percent }}%</span>
                                        <span class="pull-right user-count">{{ $product->crowdfunding->user_count }}名支持者</span>
                                    </div>
                                    <!-- 如果众筹状态是众筹中，则输出提示语 -->
                                    @if ($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                        <div>此项目必须在
                                            <span class="text-red">{{ $product->crowdfunding->end_at->format('Y-m-d H:i:s') }}</span>
                                            前得到
                                            <span class="text-red">￥{{ $product->crowdfunding->target_amount }}</span>
                                            的支持才可成功，
                                            <!-- Carbon 对象的 diffForHumans() 方法可以计算出与当前时间的相对时间，更人性化 -->
                                            筹款将在<span class="text-red">{{ $product->crowdfunding->end_at->diffForHumans(now()) }}</span>结束！
                                        </div>

                                    @endif
                                </div>
                            @else
                            <!-- 原普通商品模块开始 -->
                            @if($product->type == \App\Models\Product::TYPE_SECKILL)

                            <div class="price">
                                <div style="color:white;background: {{!$product->seckill->is_before_start && !$product->seckill->is_after_end ? '#c920cc' : 'black'}};font-size: 14px;padding:0 10px;">
                                    <div style="display: inline-block;">水货秒杀</div>
                                    @if($product->seckill->is_before_start)
                                    <div style="display: inline-block;float: right">距离开始：<div  id="from_skill_start" style="display: inline-block" ></div> </div>
                                    @elseif(!$product->seckill->is_before_start && !$product->seckill->is_after_end)
                                    <div style="display: inline-block;float: right">距离结束：<div  id="from_skill_end" style="display: inline-block"></div> </div>
                                    @else
                                    <div style="display: inline-block;float: right">抢购已结束 </div>
                                    @endif
                                </div>
                                <label style="color: red;font-size: 16px;">秒杀价:</label><em>￥</em>
                                <span id="sku_price">{{ $product->price }}</span>
                            </div>
                            @elseif($product->type == \App\Models\Product::TYPE_CROWDFUNDING)
                            <div class="price"><label  style="color: #2ab27b;font-size: 16px;">众筹价:</label><em>￥</em><span id="sku_price">{{ $product->price }}</span></div>
                            @else
                           <div class="price"><label>价格</label><em>￥</em><span id="sku_price">{{ $product->price }}</span></div>
                           @endif
                                    <div class="sales_and_reviews">
                                <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
                                <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
                                <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
                            </div>
                            @endif
                            <div class="skus">
                                    @foreach($select_attr as $k=>$attr)
                                       <label>{{$attr['name']}}</label>
                                        @foreach($attr['attribute'] as $val)
                                            <span onclick="click_attr(this);" data-group="{{$k}}" class="sku_btn btn btn-default" data-id="{{$val['id']}}">
                                            {{{$val['attr_val']}}}
                                             </span>
                                            @endforeach
                                        <br />
                                    @endforeach
                            </div>

                            <div class="cart_amount">

                                <label>数量</label>
                                @if($product->type == \App\Models\Product::TYPE_SECKILL)
                                <input type="text" disabled class="form-control input-sm" value="1"><span>件（秒杀商品每次只能购买一件）</span><span class="stock"></span></div>

                            @else
                                    <input type="text" class="form-control input-sm" value="1"><span>件</span><span class="stock"></span></div>
                                    @endif

                            <div class="buttons">
                                    @if($favorite)
                                        <button class="btn btn-danger btn-disfavor">取消收藏</button>
                                    @else
                                        <button class="btn btn-success btn-favor">❤ 收藏</button>
                                    @endif
                                    <!-- 众筹商品下单按钮开始 -->
                                        @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                            @if(Auth::check())
                                                @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                                    <button class="btn btn-primary btn-crowdfunding">参与众筹</button>
                                                @else
                                                    <button class="btn btn-primary disabled">
                                                        {{ \App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status] }}
                                                    </button>
                                                @endif
                                            @else
                                                <a class="btn btn-primary" href="{{ route('login') }}">请先登录</a>
                                            @endif
                                        <!-- 秒杀商品下单按钮开始 -->
                                        @elseif($product->type === \App\Models\Product::TYPE_SECKILL)
                                            @if(Auth::check())
                                                @if($product->seckill->is_before_start)
                                                    <button class="btn btn-primary btn-seckill disabled countdown">抢购未开始</button>
                                                @elseif($product->seckill->is_after_end)
                                                    <button class="btn btn-primary btn-seckill disabled">抢购已结束</button>
                                                @else
                                                    <button class="btn btn-primary btn-seckill">立即抢购</button>
                                                @endif
                                            @else
                                                <a class="btn btn-primary" href="{{ route('login') }}">请先登录</a>
                                            @endif
                                        <!-- 秒杀商品下单按钮结束 -->
                                        @else
                                            <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
                                    @endif
                                    <!-- 众筹商品下单按钮结束 -->
                            </div>
                        </div>
                    </div>
                    <div class="product-detail">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab">商品详情</a></li>
                            <li role="presentation"><a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab">用户评价</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                                <!-- 产品属性开始 -->
                                <div class="properties-list">
                                    <div class="properties-list-title">产品参数：</div>
                                    <ul class="properties-list-body">
                                @foreach($unique_attr as $k=>$v)
                                    <span style="width: 30%;display: inline-block;">
                                        {{$v->name}}：{{$v->val}}
                                    </span>
                                    @endforeach
                                    </ul>
                                </div>

                                    <div class="product-description">
                                {!! $product->description !!}
                                    </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                                <!-- 评论列表开始 -->
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <td>用户</td>
                                        <td>商品</td>
                                        <td>评分</td>
                                        <td>评价</td>
                                        <td>时间</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{ $review->order->user->name }}</td>
                                            <td>{{ $review->productSku->title }}</td>
                                            <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                            <td>{{ $review->review }}</td>
                                            <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <!-- 评论列表结束 -->
                            </div>
                        </div>
                    </div>
                    <!-- 猜你喜欢开始 -->
                    @if(count($similar) > 0)
                        <div class="similar-products">
                            <div class="title">猜你喜欢</div>
                            <div class="row products-list">
                                @foreach($similar as $product)
                                    <div class="col-xs-3 product-item">
                                        <div class="product-content">
                                            <div class="top">
                                                <div class="img">
                                                    <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                                        <img src="{{ $product->full_image }}" alt="">
                                                    </a>
                                                </div>
                                                <div class="price"><b>￥</b>{{ $product->price }}</div>
                                                <div class="title">
                                                    <a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                @endif
                <!-- 猜你喜欢结束 -->

                </div>
            </div>
        </div>
    </div>
@endsection
<style>

</style>
@section('scriptsAfterJs')
    <!-- 如果是秒杀商品并且尚未开始秒杀，则引入 momentjs 类库 -->
    @if($product->type == \App\Models\Product::TYPE_SECKILL)
        <script src="https://cdn.bootcss.com/moment.js/2.22.1/moment.min.js"></script>
    @endif

    <script>
        var check_arr = []; //用户当前选择的商品属性数组['属性名组id'=>属性值ID]
        var sku_arr = []; //现有的SKU
        @if(count($select_attr)) //有可选属性就把skuid先置为零，选完属性再变化
        var skuid = 0;
        @elseif(isset($skus[0]) && isset($skus[0]['id']))
        var skuid = {{$skus[0]['id']}};
        @else
        var skuid = 0;
        @endif
        var count = {{count($select_attr)}};
        var check_count = 0; //已经选择的属性数量
        @foreach($skus as $sku)
        sku_arr.push({'key':"{{$sku->attributes}}", 'val':{price:"{{$sku->price}}",stock:{{$sku->stock}}, id:{{$sku->id}}} });
        @endforeach

        function click_attr(self) {
            var group = $(self).data('group');
            if (!check_arr[group])check_count++;
            check_arr[group] = $(self).data('id');
            var id_str = check_arr.join(',');
            if (id_str[0] == ',') {
                id_str = id_str.substr(1,id_str.length);
            }

            if (count == check_count) {
                var isfind = false;
                var key = '';
                for(var i = 0; i < sku_arr.length; i++) {
                    if (sku_arr[i].key.split(',').sort().join(',') == id_str.split(',').sort().join(',')) {
                        isfind = true;
                        key = i;
                        break;
                    }
                }
                if (isfind) {
                    $("#sku_price").text(sku_arr[key].val.price);
                    $('.product-info .stock').text('库存：' + sku_arr[key].val.stock + '件');
                    skuid = sku_arr[key].val.id;
                } else {
                    $("#sku_price").text('暂无库存');
                    $('.product-info .stock').text('库存：0 件');
                    skuid = 0;
                }
            }


            $(".sku_btn[data-group="+group+"]").removeClass("my_check_attr");
            $(self).addClass('my_check_attr');

        }

        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});

            /*$('.sku-btn').click(function () {

                $('.product-info .price span').text($(this).data('price'));
                $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
            });*/


        // 监听收藏按钮的点击事件
        $('.btn-favor').click(function () {
            // 发起一个 post ajax 请求，请求 url 通过后端的 route() 函数生成。
            axios.post('{{ route('products.favor', ['product' => $product->id]) }}')
                .then(function () { // 请求成功会执行这个回调
                    swal('操作成功', '', 'success').then(function () {
                        location.reload();
                    });
                }, function(error) { // 请求失败会执行这个回调
                    // 如果返回码是 401 代表没登录
                    if (error.response && error.response.status === 401) {
                        swal('请先登录', '', 'warning')
                            .then(function () {
                                location.href='/login';
                            });
                    } else if (error.response && error.response.data.msg) {
                        // 其他有 msg 字段的情况，将 msg 提示给用户
                        swal(error.response.data.msg, '', 'error');
                    }  else {
                        // 其他情况应该是系统挂了
                        swal('系统错误', '', 'error');
                    }
                });
        });

         //取消收藏按钮
         $('.btn-disfavor').click(function () {
                axios.delete('{{ route('products.disfavor', ['product' => $product->id]) }}')
                    .then(function () {
                        swal('操作成功', '', 'success')
                            .then(function () {
                                location.reload();
                            });
                    });
         });


            // 加入购物车按钮点击事件
            $('.btn-add-to-cart').click(function () {
                var amount = $('.cart_amount input').val(); //库存
                if(count != check_arr.length){
                    swal('请先选择完商品属性', '', 'error');
                    return false;
                }else if (skuid == 0) {
                    swal('当前商品暂无库存', '', 'error');
                    return false;
                } else if (amount <= 0) {
                    swal('购买数不能小于0', '', 'error');
                    return false;
                }
                // 请求加入购物车接口
                axios.post('{{ route('cart.add') }}', {
                    sku_id: skuid,
                    amount: amount,
                })
                    .then(function () { // 请求成功执行此回调
                        swal({
                            title:"加入购物车成功",
                            text:"",
                            icon:"success",
                            buttons: {

                                confirm: {
                                    text: "前往结算",
                                    value: true,
                                    visible: true,
                                    className: "",
                                    closeModal: true
                                },
                                cancel: {
                                    text: "继续购物",
                                    value: false,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                }
                            }
                        }).then(function (value) {
                            if (value) {
                                location.href='{{route('cart.index')}}';
                            }
                        });


                    }, function (error) { // 请求失败执行此回调
                        if (error.response.status === 401) {

                            // http 状态码为 401 代表用户未登陆
                            swal('请先登录', '', 'warning')
                                .then(function () {
                                    location.href='/login';
                                });

                        } else if (error.response.status === 422) {

                            // http 状态码为 422 代表用户输入校验失败
                            var html = '<div>';
                            _.each(error.response.data.errors, function (errors) {
                                _.each(errors, function (error) {
                                    html += error+'<br>';
                                })
                            });
                            html += '</div>';
                            swal({content: $(html)[0], icon: 'error'})
                        } else if (error.response && error.response.data.msg) {
                            // 其他有 msg 字段的情况，将 msg 提示给用户
                            swal(error.response.data.msg, '', 'error');
                        }  else {

                            // 其他情况应该是系统挂了
                            swal('系统错误', '', 'error');
                        }
                    })
            });




            // 参与众筹 按钮点击事件
            $('.btn-crowdfunding').click(function () {
                // 判断是否选中 SKU
                var amount = $('.cart_amount input').val(); //库存
                if(count != check_arr.length){
                    swal('请先选择完商品属性', '', 'error');
                    return false;
                }else if (skuid == 0) {
                    swal('当前商品暂无库存', '', 'error');
                    return false;
                } else if (amount <= 0) {
                    swal('购买数不能小于0', '', 'error');
                    return false;
                }
                // 把用户的收货地址以 JSON 的形式放入页面，赋值给 addresses 变量
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                // 使用 jQuery 动态创建一个表单
                var $form = $('<form class="form-horizontal" role="form"></form>');
                // 表单中添加一个收货地址的下拉框
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">选择地址</label>' +
                    '<div class="col-sm-9">' +
                    '<select class="form-control" name="address_id"></select>' +
                    '</div></div>');
                // 循环每个收货地址
                addresses.forEach(function (address) {
                    // 把当前收货地址添加到收货地址下拉框选项中
                    $form.find('select[name=address_id]')
                        .append("<option value='" + address.id + "'>" +
                            address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone +
                            '</option>');
                });
                // 添加收货地址按钮
                $form.append('<a class="btn btn-success" style="float:left;margin-left:20px;" href="{{route('user_addresses.create')}}" >添加收货地址</a>');
                // 调用 SweetAlert 弹框
                swal({
                    text: '参与众筹',
                    content: $form[0], // 弹框的内容就是刚刚创建的表单
                    buttons: ['取消', '确定']
                }).then(function (ret) {
                    // 如果用户没有点确定按钮，则什么也不做
                    if (!ret) {
                        return;
                    }
                    // 构建请求参数
                    var req = {
                        address_id: $form.find('select[name=address_id]').val(),
                        amount: amount, //这个是全局变量
                        sku_id: skuid   //这个也是全局变量
                    };
                    // 调用众筹商品下单接口
                    axios.post('{{ route('crowdfunding_orders.store') }}', req)
                        .then(function (response) {
                            // 订单创建成功，跳转到订单详情页
                            swal('订单提交成功', '', 'success')
                                .then(() => {
                                location.href = '/orders/' + response.data.id;
                        });
                        }, function (error) {
                            // 输入参数校验失败，展示失败原因
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error+'<br>';
                                    })
                                });
                                html += '</div>';
                                swal({content: $(html)[0], icon: 'error'})
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('系统错误', '', 'error');
                            }
                        });
                });
            });


            //秒杀商品倒计时
            @if($product->type == \App\Models\Product::TYPE_SECKILL)
                @if($product->seckill->is_before_start)
                // 如果是秒杀商品并且尚未开始秒杀
                // 将秒杀开始时间转成一个 moment 对象
                var startTime = moment.unix({{ $product->seckill->start_at->getTimestamp() }});
                // 设定一个定时器
                var hdl = setInterval(function () {
                    // 获取当前时间
                    var now = moment();
                    // 如果当前时间晚于秒杀开始时间
                    if (now.isAfter(startTime)) {
                        // 将秒杀按钮上的 disabled 类移除，修改按钮文字
                        $('.btn-seckill').removeClass('disabled').removeClass('countdown').text('立即抢购');
                        // 清除定时器
                        clearInterval(hdl);
                        return;
                    }

                    // 获取当前时间与秒杀开始时间相差的小时、分钟、秒数
                    var dayDiff = startTime.diff(now, 'days');
                    var hourDiff = startTime.diff(now, 'hours')%24;
                    var minDiff = startTime.diff(now, 'minutes') % 60;
                    var secDiff = startTime.diff(now, 'seconds') % 60;
                    hourDiff = hourDiff < 10 ? '0' + hourDiff : hourDiff;
                    minDiff  = minDiff < 10 ? '0' + minDiff : minDiff;
                    secDiff  = secDiff < 10 ? '0' + secDiff : secDiff;
                    // 修改按钮的文字
                    $('.btn-seckill').text('抢购未开始');
                    $('#from_skill_start').text(dayDiff+'天 '+hourDiff+'小时 '+minDiff+'分钟 '+secDiff+"秒");

                }, 1000);
                @elseif(!$product->seckill->is_before_start && !$product->seckill->is_after_end)
                    //如果正处于秒杀时段
                    var endtTime = moment.unix({{ $product->seckill->end_at->getTimestamp() }});
                    // 设定一个定时器
                    var hdl = setInterval(function () {
                        // 获取当前时间
                        var now = moment();
                        // 如果当前时间晚于秒杀结束时间
                        if (now.isAfter(endtTime)) {
                            // 清除定时器
                            clearInterval(hdl);
                            return;
                        }

                        // 获取当前时间与秒杀开始时间相差的小时、分钟、秒数
                        console.log(endtTime.diff(now, 'days')+'--'+endtTime.diff(now, 'hours')+'---'+endtTime.diff(now, 'minutes')+'---'+endtTime.diff(now, 'seconds'));
                        var dayDiff = endtTime.diff(now, 'days');
                        var hourDiff = endtTime.diff(now, 'hours')%24;
                        var minDiff = endtTime.diff(now, 'minutes') % 60;
                        var secDiff = endtTime.diff(now, 'seconds') % 60;
                        hourDiff = hourDiff < 10 ? '0' + hourDiff : hourDiff;
                        minDiff  = minDiff < 10 ? '0' + minDiff : minDiff;
                        secDiff  = secDiff < 10 ? '0' + secDiff : secDiff;
                        // 修改倒计时的文字
                        $('#from_skill_end').text(dayDiff+'天 '+hourDiff+'小时 '+minDiff+'分钟 '+secDiff+"秒");
                    }, 1000);
                @endif
            @endif

            // 秒杀按钮点击事件
            $('.btn-seckill').click(function () {
                // 如果秒杀按钮上有 disabled 类，则不做任何操作
                if($(this).hasClass('disabled')) {
                    return;
                }
                var amount = $('.cart_amount input').val(); //库存
                if(count != check_arr.length){
                    swal('请先选择完商品属性', '', 'error');
                    return false;
                }else if (skuid == 0) {
                    swal('当前商品暂无库存', '', 'error');
                    return false;
                }

                // 把用户的收货地址以 JSON 的形式放入页面，赋值给 addresses 变量
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                // 使用 jQuery 动态创建一个下拉框
                // 把用户的收货地址以 JSON 的形式放入页面，赋值给 addresses 变量
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                // 使用 jQuery 动态创建一个表单
                var $form = $('<form class="form-horizontal" role="form"></form>');
                // 表单中添加一个收货地址的下拉框
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">选择地址</label>' +
                    '<div class="col-sm-9">' +
                    '<select class="form-control" name="address_id" id="address_id"></select>' +
                    '</div></div>');
                // 循环每个收货地址
                addresses.forEach(function (address) {
                    // 把当前收货地址添加到收货地址下拉框选项中
                    $form.find('select[name=address_id]')
                        .append("<option value='" + address.id + "'>" +
                            address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone +
                            '</option>');
                });
                // 添加收货地址按钮
                $form.append('<a class="btn btn-success" style="float:left;margin-left:20px;" href="{{route('user_addresses.create')}}" >添加收货地址</a>');
                swal({
                    text: '选择收货地址',
                    content: $form[0],
                    buttons: ['取消', '确定']
                }).then(function (ret) {
                    // 如果用户没有点确定按钮，则什么也不做
                    if (!ret) {
                        return;
                    }
                    // 构建请求参数
                    var address = _.find(addresses, {id: parseInt($("#address_id").val())});
                    var req = {
                        address: _.pick(address, ['province','city','district','address','zip','contact_name','contact_phone']),
                        sku_id: skuid
                    };
                    // 调用秒杀商品下单接口
                    axios.post('{{ route('seckill_orders.store') }}', req)
                        .then(function (response) {
                            swal('订单提交成功', '', 'success')
                                .then(() => {
                                location.href = '/orders/' + response.data.id;
                        });
                        }, function (error) {
                            // 输入参数校验失败，展示失败原因
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error+'<br>';
                                    })
                                });
                                html += '</div>';
                                swal({content: $(html)[0], icon: 'error'})
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('系统错误', '', 'error');
                            }
                        });
                });
            });


        });
    </script>
@endsection