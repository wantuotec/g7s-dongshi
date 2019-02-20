## xxxxxxxxx

### 接口名称： <?php echo $method . "\r\n"; ?>

### 接口说明
    xxxxxxxxxxxxxxxx

### 业务逻辑
    xxxxxxxxxxxxxxxx

### 文档作者
         <?php echo date('Y-m-d') . "\r\n"; ?>

### 使用身份


### 需要登录


### 输入参数

<?php foreach($params_fields as $key => $value) : ?>
<?php echo "\t" . $value . "\r\n";  ?>
<?php endforeach; ?>


### 输出数据格式

<?php if (isset($response_var['data']['list'][0])) : ?>
    <?php $output_data = $response_var['data']['list'][0]; ?>
<?php else: ?>
    <?php $output_data = $response_var['data']; ?>
<?php endif;?>

<?php foreach($output_data as $key => $value) : ?>
<?php echo "\t" . $key . "\r\n";  ?>
<?php endforeach; ?>

## 参考数据

#####输入参数
    <?php echo "\t" . $params_json; ?>

#####输出数据格式
    解析后JSON格式实例
    
    <?php echo "\t" . json_encode($response_var); ?>

    解析后PHP格式示例
<?php
    $arr_response = explode("\n", $response);
?>
<?php
    foreach($arr_response as $value) {
        echo "\t" . $value . "\r\n";
    }

?>

###错误码参考