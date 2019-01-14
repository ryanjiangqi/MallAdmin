{include file="comm/header" /}
{include file="comm/foot" /}
<script>
    parent.layer.alert('<?php echo(strip_tags($msg));?>', {
        icon: 1,
        title: '温馨提示',
        skin: 'layui-layer-default' //样式类名
    }, function () {
        window.parent.location.href = "<?php echo($url);?>";
    });
</script>
