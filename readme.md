## 电子商城
本项目基于Laravel-china教程6《电商进阶》，在此基础上进行扩展开发
<br /><br />线上demo：https://shop.wuxxin.com
## 扩展内容
1、用户模块
- 支持手机号注册、登录、找回密码（腾讯云验证码和短信发送）
- 提供微信网页授权登录、小程序登录、注册API支持

2、商品模块
- 扩展商品SKU为多维度。（在原本的products(商品表)+product_skus（SKU表）基础上增加商品属性表（product_attributes）和商品属性值表（attributes）,以属性值表ID字符串构成SKU)
- 修改商品分类表path字段，将本身id也加入其中（方便搜索），去除is_directory字段
- 修改秒杀验证订单是否重复逻辑，由mysql查询订单改为redis查询，减缓mysql压力

3、搜索模块
- 索引结构中增加一个Nested 对象search_properties的字段，该字段记录是否参与筛选，后台在设置商品属性时设置是否参与分面搜索即可（原来是直接聚合所有属性，然后展示在商品列表顶部的属性列表栏中供用户点击属性，筛选商品，现在后台可控制属性是否参与筛选列表的展示，像有些属性就没必要列出来给用户筛选了，比如手机有个属性是生产日期，这种属性对用户来说无关紧要，就选择不用参与分面搜索，这样就不会列出来，当然，所有的属性都是会参与关键字搜索的，不会受到是否参与分面搜索的影响）

4、API模块
- 使用DingoApi进行API开发，为小程序提供完整接口支持，支持腾讯云COS文件存储

## 本项目模块概述
1、用户模块
- 登录注册（找回密码）
- 商品收藏
- 收货地址
- 查看订单
- 购物流程：选择商品-》下单-》付款-》确认收货-》发表评价、申请退款

2、商品模块
- 商品无限级分类
- 后台添加商品、设置商品属性、设置商品库存
- 前台展示显示商品列表，ES搜索引擎提供强大搜索功能
- 众筹商品
- 秒杀商品

3、订单模块
- 用户订单页面显示已经提交过的订单，管理员后台显示已支付订单
- 后台输入物流信息，进行发货操作

5、优惠券模块
- 多种优惠方案（满减、打折）
- 后台设置优惠券，前台提供优惠券使用接口

6、支付模块
- PC端前台提供微信/支付宝扫码支付，同时提供分期付款功能
- 小程序端提供微信支付
- 前台提供用户申请退款接口、后台审核是否同意退款、众筹失败时自动退款、一键退款分期订单

7、其他
轮播图管理、站点信息管理、会员管理、权限管理等

## 安装方法
- 1、git clone或者下载解压到本地
- 2、将public设置为网站根目录，调整storage目录权限，在public目录下手动创建uploads文件夹并分配给服务器权限
- 3、composer install
- 4、npm install
- 5、npm run production
- 6、启动redis
- 7、安装好[jdk1.8](https://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html)配置好java环境
- 8、下载[ElasticSearch](https://www.elastic.co/downloads/past-releases)以及它的[中文分词插件](https://github.com/medcl/elasticsearch-analysis-ik/releases)，两个的版本要对应上，把下载的插件解压到es的plugins目录下重命名为ik
- 9、在elasticSearch的config目录下新建 analysis/synonyms.txt，不用写内容都行，这个文件主要用于同义词搜索，没有不行（最终路径看起来像这样 /usr/local/src/elasticsearch-6.3.0/config/analysis）
- 10、linux: 新建一个用户，切换到该用户，启动ES。windows直接执行bin下的bat脚本即可                           
- 11、启动mysql，创建数据库，配置env数据库，运行php artisan migrate:fresh
- 12、后台管理员账号为admin 密码为admin  前台用户账号为123456@qq.com 密码为123456
- 13、配置好队列和定时任务，
- 提示:APP_DEBUG为true时，注册时的短信验证码不会发送，固定为1234，APP_ENV为local时，支付回调使用NGROK_URL配置的网址，证书目录需要自己手动在resources下新建wechat_pay目录，把微信证书丢里面

