<?php
/**
 * User: loveyu
 * Date: 2016/5/15
 * Time: 6:53
 * @var string $__key_word
 */
?>
<div id="SearchPage">
	<div class="page-header">
		<h1><?php echo $__key_word; ?>
			<small>搜索</small>
		</h1>
	</div>

	<div class="tab-list">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#PictureTab" aria-controls="PictureTab" role="tab" data-toggle="tab">图片</a></li>
			<li role="presentation"><a href="#GalleryTab" aria-controls="GalleryTab" role="tab" data-toggle="tab">图集</a></li>
			<li role="presentation"><a href="#PostTab" aria-controls="PostTab" role="tab" data-toggle="tab">文章</a></li>
		</ul>
		<div class="tab-content search-tabs-content">
			<div class="tab-pane active" id="PictureTab">
				<div class="Search-Loading">Loading...</div>
			</div>
			<div class="tab-pane" id="GalleryTab">
				<div class="Search-Loading">Loading...</div>
			</div>
			<div class="tab-pane" id="PostTab">
				<div class="Search-Loading">Loading...</div>
			</div>
		</div>

	</div>
</div>