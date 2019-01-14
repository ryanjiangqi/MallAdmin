$(function () {
// 假设服务端ip为127.0.0.1
    ws = new WebSocket("ws://47.98.113.6:1234");//127.0.0.1
    ws.onopen = function () {
        console.log("连接成功");
        var pcArray = new Array()
        $('[name="pc_no"]').each(function () {

            pcArray.push($(this).attr('data'));
        });
        ws.send(pcArray);
        console.log("fasong：" + pcArray);
    };
    ws.onmessage = function (e) {
        $("#x-link").html(e.data);
        console.log("收到服务端的消息:xxx");
    };
});

function layout(base64Info) {
    layer.confirm('确认要为该顾客下机吗？', function (index) {
        // 假设服务端ip为127.0.0.1
        ws = new WebSocket("ws://47.98.113.6:2346");//127.0.0.1
        ws.onopen = function () {
            ws.send(base64Info);
            console.log("fasong：" + base64Info);
        };
        ws.onmessage = function (e) {
            console.log("收到服务端的消息：" + e.data);
        };
        layer.msg('已下机!', {icon: 6, time: 1000});
    });


}