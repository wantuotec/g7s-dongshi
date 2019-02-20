<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">

            <div class="portlet portlet-custom-style box light-grey">

                <div class="portlet-title">
                    <div class="caption"><i class="icon-reorder"></i>版本编辑信息</div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                    </div>
                </div>

                <div class="portlet-body form">

                <form action="#" class="form-horizontal form-bordered ">
                    <?php foreach($data as $value): ?>
                        <div class="row-fluid">
                            <div class="span12 ">
                                <div class="control-group">
                                    <label class="control-label">版本说明</label>
                                    <div class="controls">
                                        <span class="text" style="display: inline-block;font-size: 18px;font-weight: 500;"><?php echo $value['title']; ?>&nbsp&nbsp&nbsp&nbsp更新日期&nbsp&nbsp<?php echo $value['create_time']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span12 ">
                                <div class="control-group">
                                    <label class="control-label">版本描述</label>
                                    <div class="controls">
                                        <span class="text" style="height: 300px;""><?php echo $value['content'] ?></span>
                                    </div>
                                </div>
                            </div>
                    </div>
        <?php endforeach; ?>
</form>