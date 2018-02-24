<?php
$server = new swoole_websocket_server("0.0.0.0", 9501);

/*$server->set(array(
    'ractor_num' => 1,    //主进程中线程数量
    'worker_num' => 2,    //工作进程数量
    'daemonize' => 0,  //是否守护进程
    'log_file' => '/data/swoole.log',    //日志存储路径
    'dispatch_mode' => 5,     //1平均分配，2按FD取摸固定分配，3抢占式分配，默认为取模(dispatch=2)'
));*/

$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";//$request->fd 是客户端id
    //$server->push($request->fd , $request->fd);//循环广播
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    //echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    //$frame->fd 是客户端id，$frame->data是客户端发送的数据
    //服务端向客户端发送数据是用 $server->push( '客户端id' ,  '内容')
    $data=$frame->data;
    $datas = json_decode($data,true);
    var_dump($datas);
    $server->bind($frame->fd, $datas['room_id']);
    foreach($server->connections as $fd){
        unset($datas['isnew']);
        if ($fd==$frame->fd){
            $datas['isnew']=1;
        }
        $fdinfo = $server->connection_info($fd);

        if ($fdinfo['uid']==$datas['room_id']){
            $send=json_encode($datas);
            $server->push($fd , $send);//循环广播
        }else{
            continue;
        }

    }
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();