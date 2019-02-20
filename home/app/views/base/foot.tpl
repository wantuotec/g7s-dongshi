<script type="text/javascript">
<!--
$(document).ready(function(){
    $.BKD.slide();
    //$.BKD.syncHeight();
    //$.BKD.scroll();
    $.BKD.cloesNotice();
    $.BKD.tableClass();
    // 自动给表单赋值
    $.BKD.form_init(<?php echo !empty($search) && is_array($search) ? json_encode($search) : "''"; ?>);

})

if (/MSIE/.test(navigator.userAgent) || /Trident/.test(navigator.userAgent)) {
    alert('请不要使用IE浏览器，IE浏览器内核老旧，目前不再支持\n如果您使用的是360、搜狐、百度等多核浏览器，请使用极速模式\n强烈推荐使用谷歌、火狐浏览器');
}
-->
 </script> 
    </body>
</html>