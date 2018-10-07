<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">创建</h3>

        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{{route('skus.index')}}" class="btn btn-sm btn-default"><i class="fa fa-list"></i>&nbsp;列表</a>
            </div> <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{{route('skus.index')}}" class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;返回</a>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
        @if($sku->id)
            <form id="sku_form" method="put" action="{{route('skus.update', $sku->id)}}" class="form-horizontal">
            <input type="hidden" name="id" value="{{$sku->id}}">
        @else
            <form id="sku_form" method="post" action="{{route('skus.store')}}" class="form-horizontal">
        @endif
            {{csrf_field()}}
        <div class="box-body">

            <div class="fields-group">

                <div class="form-group  ">

                    <label class="col-sm-2  control-label">选择商品<i style="color:red;"> *</i></label>

                    <div class="col-sm-8">

                        <select required id="product_id" class="form-control" style="width: 100%;" name="product_id" tabindex="-1" onchange="showAttributes()">
                            <option value=""></option>
                            @foreach($products as $item)
                                <option value="{{$item->id}}"
                                @if($sku->product_id == $item->id)
                                selected="selected"
                                 @endif
                                >{{$item->text}}</option>
                            @endforeach
                        </select>


                    </div>
                </div>

                <div id="attributes">
                    @foreach($attributes as $v)
                    <div class="form-group myshow">
                            <label for="price" class="col-sm-2  control-label">
                               {{$v->name}}
                            <i style="color:red;"> *</i></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input type="text" value="{{$v->attr_val}}" data-id="{{$v->pro_attr_id}}" class="form-control price product_attributes" placeholder="输入 {{$v->name}}">
                                </div>
                            </div>
                    </div>
                        @endforeach
                </div>
                <div class="form-group  ">

                    <label for="description" class="col-sm-2  control-label">描述</label>

                    <div class="col-sm-8">


                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                            <input type="text" id="description" name="description" value="{{old('description', $sku->description)}}" class="form-control description" placeholder="输入 描述">


                        </div>


                    </div>
                </div>
                <div class="form-group  ">

                    <label for="price" class="col-sm-2  control-label">价格<i style="color:red;"> *</i></label>

                    <div class="col-sm-8">


                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>

                            <input type="number" step="0.01" id="price" name="price" value="{{old('price', $sku->price)}}" class="form-control price" placeholder="输入 价格">

                        </div>


                    </div>
                </div>
                <div class="form-group  ">

                    <label for="stock" class="col-sm-2  control-label">库存<i style="color:red;"> *</i></label>

                    <div class="col-sm-8">


                        <div class="input-group">


                        <div class="input-group"><span class="input-group-btn"></span>
                                <input style="width: 100px; text-align: center;" type="number" id="stock" name="stock" value="{{old('stock', $sku->stock)}}" class="form-control stock initialized" placeholder="输入 库存">
                                <span class="input-group-btn"></span>
                        </div>
                        </div>


                    </div>
                </div>


            </div>

        </div>
        <!-- /.box-body -->
        <div class="box-footer">

            <div class="col-md-2">

            </div>
            <div class="col-md-8">

                <div class="btn-group pull-right">
                    <button onclick="submit_form()" type="button" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> 提交">提交</button>
                </div>

                <div class="btn-group pull-left">
                    <button type="reset" class="btn btn-warning">重置</button>
                </div>

            </div>

        </div>


        <!-- /.box-footer -->
    </form>
</div>
<style>
    @-webkit-keyframes onshow {
        0%{opacity: 0.1}
        50%{opacity: 0.5}
        100%{opacity: 1}
    }
    .myshow{
        -webkit-animation: 0.8s onshow linear;
    }
</style>
<script>

    //获取商品属性
    function showAttributes() {
        var product_id = $("#product_id").val();
        if (product_id == '' || product_id == undefined) {
            $("#attributes").html('');
        } else {
            var url = '/admin/api/attributes/'+product_id;
            $.ajax({
                type:'get',
                url:url,
                dataType:'json',
                success:function (data) {
                    if (data.length == 0) {
                        /*swal({
                            title: '该商品没有可选属性',
                            type: 'warning'
                        });*/
                        $("#attributes").html('');
                    } else {
                        var el = '';
                        for (var i = 0; i < data.length; i++) {
                            el += '<div class="form-group myshow"><label for="price" class="col-sm-2  control-label">'+data[i].name+'<i style="color:red;"> *</i></label><div class="col-sm-8"><div class="input-group"><span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span><input type="text" value="" data-id="'+data[i].id+'" class="form-control price product_attributes" placeholder="输入'+data[i].name+'"></div></div></div>';
                        }

                        $("#attributes").html(el);
                    }
                },
                error:function (msg) {
                    swal('系统内部错误', '', 'error');
                }
            })
        }
    }


    //laravel-admin扩展起来很麻烦，最后考虑直接ajax提交表单了
    function submit_form() {
        var formData = $("#sku_form").serialize();
        var url = $("#sku_form").attr('action');
        var method = $("#sku_form").attr('method');
        var is_good = true;
        var attributes = [];
        if ( $(".product_attributes").length == 0) {
            /*swal('请先填写商品属性值再提交', '', 'error');
            return false;*/
        }
        $(".product_attributes").each(function (k,v) {
            if ($(v).val() == '') {
                is_good = false;
                return;
            }
            attributes.push({id:$(v).data('id'), value:$(v).val()});
        });

        if(!is_good) {
            swal('请填写完所有的商品属性后再提交', '', 'error');
            return false;
        }

        formData+='&attributes='+JSON.stringify(attributes);;

        $.ajax({
            type:method,
            url:url,
            dataType:'json',
            data:formData,
            success:function (data) {
                //code是200并且有输出进入这里
                swal({
                title:"操作成功",
                text:"OK",
                type:"success",
                showCancelButton:true,
                confirmButtonText:"继续添加",
                cancelButtonText:"返回列表",
                closeOnConfirm:false,
                closeOnCancel:false,
            },function(isConfirm){
                    if (isConfirm) {
                        location.href = '/admin/skus/create';
                    } else {
                        location.href = '/admin/skus';
                    }
                });
            },
            error:function (msg) {

                /*
                1.状态码不是200
                2. 返回数据类型不是JSON
                3. 网络中断
                4. 后台响应中断
                */
                if (msg.status == 422) {
                    console.log(msg.responseJSON.errors);
                    var obj = msg.responseJSON.errors;
                    var html = '';
                    Object.keys(obj).forEach(function(key){
                        html+=obj[key].join();
                    });
                    swal(html, '', 'error');
                } else if(msg.responseJSON) {
                    swal(msg.responseJSON.msg,'', 'error');
                } else {
                    swal('系统内部错误', '', 'error');
                }

            }
        })

    }

</script>