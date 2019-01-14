/*弹出层*/

/*
 参数解释：
 title	标题
 url		请求的url
 id		需要操作的数据id
 w		弹出层宽度（缺省调默认值）
 h		弹出层高度（缺省调默认值）
 */
function x_admin_show(title, url, w, h) {
    if (title == null || title == '') {
        title = false;
    }
    ;
    if (url == null || url == '') {
        url = "404.html";
    }
    ;
    if (w == null || w == '') {
        w = 800;
    }
    ;
    if (h == null || h == '') {
        h = ($(window).height() - 50);
    }
    ;
    layer.open({
        type: 2,
        area: [w + 'px', h + 'px'],
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        shade: 0.4,
        title: title,
        content: url
    });
}

function full(url) {
    var index = layer.open({
        type: 2,
        shade: 0.4,
        title: false,
        content: url
    });
    layer.full(index);
}

/*关闭弹出框口*/
function x_admin_close() {
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}

function upload_image(url) {
    layer.open({
        type: 2,
        area: ['600px', '500px'],
        fix: false, //不固定
        maxmin: false,
        shadeClose: true,
        shade: 0.4,
        title: '上传图片',
        content: url
    });
}

function deletedImg(obj) {
    $(obj).parent("li").remove()
}

function changeStatusComm(url, status,statusmsg, id) {
    layer.confirm('确认要更新为' + statusmsg + '吗？', function (index) {
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id, status: status
            },
            success: function (data) {
                layer.msg('更新成功!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });
    });
}

function deletedRow(obj, url, id) {
    layer.confirm('确认要删除吗？', function (index) {
        //发异步删除数据
        //$(obj).parents("tr").remove();
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id
            },
            success: function (data) {
                layer.msg('已删除!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });

    });
}

/*初始化layui插件*/
$(function () {
    layui.use(['element', 'laypage', 'layer', 'form'], function () {
        $ = layui.jquery;//jquery
        lement = layui.element();//面包导航
        laypage = layui.laypage;//分页
        layer = layui.layer;//弹出层
        form = layui.form();//弹出层

    })
});


