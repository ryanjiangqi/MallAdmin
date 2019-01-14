function changeDeal(url, msg, id) {
    if (msg == '未处理') {
        status = '已处理';
    }
    if (msg == '已处理') {
        status = '未处理';
    }
    layer.confirm('确认要更新为' + status + '吗？', function (index) {
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id, is_deal: status
            },
            success: function (data) {
                layer.msg('更新成功!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });
    });
}

function changeStatus(url, msg, id) {
    layer.confirm('确认要更新为' + msg + '吗？', function (index) {
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id, status: msg
            },
            success: function (data) {
                layer.msg('更新成功!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });
    });
}
