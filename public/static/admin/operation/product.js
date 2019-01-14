function node(key, value) {
    this._key = key;
    this._value = value;
}

function get(vList, key) {
    for (var i = 0; i < vList.length; i++) {
        if (vList[i]._key == key) {
            return vList[i]._value;
        }
    }
    return undefined;
}

$(function () {
    layui.use(['element', 'laypage', 'layer', 'form'], function () {
        $ = layui.jquery;//jquery
        lement = layui.element();//面包导航
        laypage = layui.laypage;//分页
        layer = layui.layer;//弹出层
        form = layui.form();//弹出层

        form.on('select(attribute_id)', function (data) {
            if (data.value == 0) {
                $("#select-parent").css('display', 'block');
            } else {
                $("#select-parent").css('display', 'none');
            }
        });
        form.on('select(items_id)', function (data) {
            location.href = '/mall/product/addproduct/itemsid/' + data.value;
        });
        form.on('checkbox(select_attr)', function (data) {
            var att = new Array();
            var key = 0;
            $("[name='attribute_list']").each(function () {
                var parentId = $(this).val();
                att.push(new node(parentId, new Array()));
            });
            if (data.elem.checked) {
                $("#attr_" + data.value + "").attr('selected', 'selected');
            } else {
                $("#attr_" + data.value + "").removeAttr('selected', '');
            }
            $("[attri='attribute_type']").each(function () {
                if ($(this).attr('selected') == 'selected') {
                    //$(this).attr('data')
                    get(att, $(this).attr('data')).push($(this).val());
                }
            });
            var checkCom = true;
            for (var s = 0; s < att.length; s++) {
                if (att[s]['_value'] == '' || att[s]['_value'] == undefined) {
                    checkCom = false;
                    break;
                }
            }
            if (checkCom) {
                var combination = new Array();
                for (var s = 0; s < att.length; s++) {
                    combination[s] = att[s]['_value'];
                }
                var newArrat = doExchange(combination)
                //console.log(newArrat);
                var html = '';
                for (var t = 0; t < newArrat.length; t++) {
                    html = html + ' <tr>';
                    var strs = new Array(); //定义一数组
                    strs = newArrat[t].split(","); //字符分割
                    var attrValue = '';
                    var attrId = '';
                    for (var c = 0; c < strs.length; c++) {
                        var strsvalue = new Array(); //定义一数组
                        strsvalue = strs[c].split("-");
                        var spit = '&nbsp;&nbsp;';
                        attrValue = attrValue + spit + strsvalue[1];
                        if (c == 0) {
                            attrId = attrId + '' + strsvalue[0];
                        } else {
                            attrId = attrId + ',' + strsvalue[0];
                        }
                    }
                    html = html + ' <th>属性组合:' + attrValue + '</th><input type="hidden" name="attribute_list_id[]" value="' + attrId + '">';
                    html = html + ' <th ><span class="x-red" >* </span><span style="float: left;">SKU: </span>';
                    html = html + '<input type="text" id="sku" name="sku[]" required="" lay-verify="required" autocomplete="off" class="layui-input" value="" style="width: 70%;">';
                    html = html + ' <th><span class="x-red" >* </span><span style="float: left;">价格: </span>';
                    html = html + '<input type="text" id="num" name="price[]" required="" lay-verify="required" autocomplete="off" class="layui-input" value="0" style="width: 70%;">';
                    html = html + ' </th><th title="删除" style="width: 40px;"><div class="layui-btn layui-btn-danger" onclick="deleteSku(this);"><i class="layui-icon"></i></div></th><tr>';
                }

                $("#attribute_html").html(html);
            } else {
                $("#attribute_html").html('');
            }
        });
    })
});

function deleteSku(obj) {
    $(obj).parent().parent('tr').remove();
}

function doExchange(arr) {
    var len = arr.length;
    // 当数组大于等于2个的时候
    if (len >= 2) {
        // 第一个数组的长度
        var len1 = arr[0].length;
        // 第二个数组的长度
        var len2 = arr[1].length;
        // 2个数组产生的组合数
        var lenBoth = len1 * len2;
        //  申明一个新数组,做数据暂存
        var items = new Array(lenBoth);
        // 申明新数组的索引
        var index = 0;
        // 2层嵌套循环,将组合放到新数组中
        for (var i = 0; i < len1; i++) {
            for (var j = 0; j < len2; j++) {
                items[index] = arr[0][i] + "," + arr[1][j];
                index++;
            }
        }
        // 将新组合的数组并到原数组中
        var newArr = new Array(len - 1);
        for (var i = 2; i < arr.length; i++) {
            newArr[i - 1] = arr[i];
        }
        newArr[0] = items;
        // 执行回调
        return doExchange(newArr);
    } else {
        return arr[0];
    }
}
