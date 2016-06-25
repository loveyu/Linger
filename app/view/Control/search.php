<form class="form-horizontal" action="<?php echo get_url("UserControlApi", "search") ?>" method="post" style="max-width: 1000px">
	<fieldset>
		<legend>搜索服务器设置</legend>
	</fieldset>
	<div class="form-group">
		<label class="control-label col-sm-3" for="elastic_status">是否启用搜索服务</label>

		<div class="col-sm-9">
			<select name="elastic_status" class="form-control" id="elastic_status">
				<?php echo html_option([
					'0' => '关闭搜索',
					'1' => '启用搜索',
				], cfg()->get('option', 'elastic_status') ? '1' : '0') ?></select>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-3" for="elastic_server">服务器地址</label>

		<div class="col-sm-9">
			<input type="text" name="elastic_server" placeholder="如：http://127.0.0.1:9200/ 斜杠结尾" id="elastic_server"
				   value="<?php echo cfg()->get('option', 'elastic_server') ?>" class="form-control">
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-3" for="elastic_index_prefix">搜索索引的index前缀</label>

		<div class="col-sm-9">
			<input type="text" name="elastic_index_prefix" placeholder="默认空，一般在转移或重建时使用" id="elastic_index_prefix"
				   value="<?php echo cfg()->get('option', 'elastic_index_prefix') ?>" class="form-control">
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-3" for="elastic_index">搜索索引名称</label>

		<div class="col-sm-9">
			<input type="text" name="elastic_index" placeholder="默认picture" id="elastic_index"
				   value="<?php echo cfg()->get('option', 'elastic_index') ?>" class="form-control">
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-9 col-sm-offset-3">
			<button class="btn btn-primary" type="submit">修改</button>
		</div>
	</div>
</form>

<div>
	<p class="help-block">请先在搜索库中创建如下结构，有需求请自行修改。</p>
	<p class="help-block text-danger">如有重建索引或其他需求，请参考DataCreate类中的相关代码，并测试</p>
	<pre><code>{
   "picture": {
      "mappings": {
         "gallery": {
            "_all": {
               "analyzer": "ik_max_word"
            },
            "properties": {
               "add_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "desc": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "detail": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "modify_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "tags": {
                  "type": "string",
                  "boost": 5,
                  "term_vector": "with_positions_offsets",
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "title": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               }
            }
         },
         "pic": {
            "_all": {
               "analyzer": "ik_max_word"
            },
            "properties": {
               "add_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "desc": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "modify_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "name": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "tags": {
                  "type": "string",
                  "boost": 5,
                  "term_vector": "with_positions_offsets",
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               }
            }
         },
         "post": {
            "_all": {
               "analyzer": "ik_max_word"
            },
            "properties": {
               "abstract": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "add_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "content": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "modify_time": {
                  "type": "date",
                  "format": "strict_date_optional_time||epoch_millis"
               },
               "route": {
                  "type": "string",
                  "boost": 8
               },
               "tags": {
                  "type": "string",
                  "boost": 5,
                  "term_vector": "with_positions_offsets",
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               },
               "title": {
                  "type": "string",
                  "boost": 8,
                  "analyzer": "ik_max_word",
                  "include_in_all": true
               }
            }
         }
      }
   }
}</code></pre>
</div>

<script>
	$(function () {
		$("form").ajaxForm(function (data) {
			if (data['status']) {
				alert_notice("更新成功");
			} else {
				alert_error(data['msg'], "更新失败");
			}
		});
	});
</script>