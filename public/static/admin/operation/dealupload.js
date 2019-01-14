$.extend({
    upload: function (option) {
        $("#upload-input").ajaxImageUpload({
            url: option.uploadurl, //上传的服务器地址
            maxNum: 10, //允许上传图片数量
            zoom: false, //允许上传图片点击放大
            allowType: ["gif", "jpeg", "jpg", "bmp", 'png'], //允许上传图片的类型
            maxSize: 2, //允许上传图片的最大尺寸，单位M
            before: function () {
            },
            success: function (data) {
                console.log(data);
            },
            error: function (e) {
                console.log(e + "error");
            }
        });
    },
    save_image: function (parentid, num, filename) {
        if (num > 1) {
            var filenameNew = filename + '[]';
        } else {
            var filenameNew = filename;
        }
        var selectNav = $(".layui-this").attr('value');
        var html = '';
        var i = 0;
        if (selectNav == 1) {
            $("li.selectimges").each(function () {
                if ($(this).attr('att') == 1) {
                    var img = $(this).attr('value');
                    html = html + '<li><img src="/static/uploadfile/100x100/' + img + '"><span class="img-deleted" onclick="deletedImg(this);">';
                    html = html + '<img src="/static/admin/images/delete.png" style="height: 20px;width: 20px;"></span>';
                    html = html + '<input type="hidden" name="' + filenameNew + '" value="' + img + '"></li>';
                    i = i + 1;
                }
            });
        }
        if (selectNav == 2) {
            $("section.image-section").each(function () {
                html = html + '<li><img src="' + $(this).children('img').attr('src') + '"><span class="img-deleted" onclick="deletedImg(this);">';
                html = html + '<img src="/static/admin/images/delete.png" style="height: 20px;width: 20px;"></span>';
                html = html + '<input type="hidden" name="' + filenameNew + '" value="' + $(this).children('input').attr('value') + '"></li>';
                i = i + 1;
            });
        }
        var exist = $('#' + parentid, window.parent.document).children('li').length;


        if ((i * 1 + exist * 1) > num) {
            layer.msg("上传图片数目不可以超过" + num + "个", function () {
            });
            return;
        }
        $('#' + parentid, window.parent.document).append(html);
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    },

    selectimage: function (Obj) {
        var val = $(Obj).attr('att');
        if (val == 0) {
            $(Obj).attr('att', '1');
            $(Obj).css('border', '1px solid red');
        }
        if (val == 1) {
            $(Obj).attr('att', '0');
            $(Obj).css('border', '1px dashed #d0d0d0');
        }

    }

});



