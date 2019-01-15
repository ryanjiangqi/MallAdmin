function changeDeal(url, id) {
    layer.confirm('确认要更新吗？', function (index) {
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id,
                status:$("#changepayment").val()
            },
            success: function (data) {
                layer.msg('更新成功!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });
    });
}

function changeStatus(url,id) {
    layer.confirm('确认要更新吗？', function (index) {
        $.ajax({
            url: url,
            type: 'Post',
            dataType: 'json',
            beforeSend: function () {
            },
            data: {
                id: id,
                status:$("#changeorderstatus").val()
            },
            success: function (data) {
                layer.msg('更新成功!', {icon: 1, time: 1000}, function () {
                    window.location.reload();
                });
            }
        });
    });
}
