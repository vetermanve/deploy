<style type="text/css">
    .deploy td span {
        display: block;
    }

    .deploy td {
        vertical-align: top;
    }
    
    hr {
        border: 0;border-bottom: 1px solid #EEE
    }

</style>
<center>
<table class="pure-table pure-table-bordered deploy">
    <thead>
    <tr>
        <th>Name</th>
        <th>update</th>
<!--        <th>branches</th>-->
<!--        <th>last commits</th>-->
        <th>reset/checkout</th>
    </tr>
    </thead>
    <?php foreach ($list as $dir => $data): ?>
    <tr>
        <td>
            <p style="font-weight: bold; white-space: nowrap"><?=ucfirst($dir) ?></p>
            <small>
            Обновлена:<br/>
            <?=$data['time']['back'] ?><br/>
            <?=$data['time']['date'] ?>
            </small>
        </td>

        <td>
            <a class="pure-button" onclick='admin.update("<?=$dir ?>", this)'>update</a>
            <hr/>
            <?=implode(" <br> ", $data['com']) ?>
        </td>
        <td>
            <a class="pure-button" onclick='admin.fixGit("<?=$dir ?>", this)'>reset branch </a>
            <hr />
            <select>
                <?php foreach ($data['branch'] as $branch): ?>
                    <option <?=strpos($branch, '*') === 0 ? 'selected' : '' ?> value="<?=trim($branch, '* '); ?>" title="<?php echo htmlentities($branch) ?>"><?php echo substr($branch,0, 40); ?></option>
                <?php endforeach; ?>
            </select>
            <a class="pure-button" onclick='admin.checkout("<?=$dir ?>", this, $(this).parent().find("select").val())'>checkout branch</a>
            <hr />
            <a onclick="$('.dev-tools-<?=crc32($dir) ?>').toggle()" >dev tools</a>
            <div class="dev-tools-<?=crc32($dir) ?>" style="display: none">
                <a class="pure-button" onclick='admin.fixGit("<?=$dir ?>", this, 1)'>reset and delete files</a>
            </div>
            <hr />
            <small><?=implode(" <br> ", $data['remote']) ?></small>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</center>
<p id="doneLog" style="white-space: pre-wrap;">
    
</p>
<script type="text/javascript">
    
    var admin = {
        rootPath : '/web/deploy',
        getGit : function (dir, el) {
            el = $(el).parent();
            var _this = this;
            _this.start();
            $.getJSON(_this.rootPath + '/getgit/', {dir:dir}, function (res) {
                _this.log(res.data, el);
                _this.stop();
            });
        },
        fixGit : function (dir, el, realClean) {
            realClean = realClean || 0;
            el = $(el).parent();
            var _this = this;
            _this.start();
            $.getJSON(_this.rootPath + '/fixgit/', {dir:dir, doClean : realClean}, function (res) {
                _this.log(res.data, el);
                _this.stop();
            });
        },
    
        checkout : function (dir, el, branch) {
            el = $(el).parent();
            var _this = this;
            _this.start();
            $.getJSON(_this.rootPath + '/checkout/', {dir:dir, branch : branch}, function (res) {
                _this.log(res.data, el);
                _this.stop();
            }).error(function (r, data, errorThrown) {
                $('#doneLog').html(r.responseText);
            });
        },
        
        update : function (dir, el) {
            el = $(el).parent();
            var _this = this;
            _this.start();
            $.getJSON(_this.rootPath + '/update/', {dir:dir}, function (res) {
                _this.log(res.data, el);
                _this.stop();
            });   
        },
        log : function (data, el) {
            $('#doneLog').html(data);
            el.find('.upLog').remove();
            data = typeof data == 'string' ? data : JSON.stringify(data) ;
            el.append( '<div class="upLog"><hr/>' + (data && data.substr(0, 150)) +' <hr/><a href="#doneLog">full log</a></div>');
        },
        start : function () {
            $('#mainTitle').addClass('blink_me');
            $('#menuLink').addClass('blink_me');
//            $('#loader').show();
//            .blink_me
        },
        stop: function () {
            $('#mainTitle').removeClass('blink_me');
            $('#menuLink').removeClass('blink_me');
//            $('#loader').hide();
        }
    }
</script>
