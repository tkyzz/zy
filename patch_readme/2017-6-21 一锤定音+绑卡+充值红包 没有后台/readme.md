### 概览
本次更新后端包括小米奖品查看页+每日推送，PHP版的红包奖励不上线

### 定时任务
####  每天10天执行推送
~~~
0 10 * * *  php /usr/local/openresty/nginx/html/php/console/run.php "request_uri=/console/xiaomiPush" 2>&1
~~~
