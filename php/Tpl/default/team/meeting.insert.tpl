
<ul class="breadcrumb">
	<li><a href="/" title=""><span class="glyphicon glyphicon-home"></span> Team</a></li>
  <span class="divider">&raquo;</span>
	<li><a href="/meeting" title="">การประชุม</a></li>
  <span class="divider">&raquo;</span>
	<li><a href="/meeting/insert" title="">เพิ่ม</a></li>
</ul>

<div class="box-white">
  <?php if($_SERVER['QUERY_STRING']=='completed'):?>
  <div class="alert alert-success">
    <a class="close" data-dismiss="alert" href="#">×</a>
    <h4 class="alert-heading">เรียบร้อยแล้ว!</h4>
   ระบบทำการบันทึกข้อมูลเรียบร้อยแล้ว
  </div>
  <?php endif?>
  <form action="<?php echo URL?>" class="form-horizontal" method="post" onsubmit="_.ajax.gourl('<?php echo URL?>','newmeeting',this);return false;">
		<div class="form-group">
      <label for="title" class="col-sm-2 control-label">หัวข้อ</label>
      <div class="col-sm-10">
        <input type="text" name="title" class="form-control" id="title" placeholder="หัวข้อ" value="<?php echo $this->content['title']?>"/>
      </div>
    </div>
		<div class="form-group">
      <label for="title" class="col-sm-2 control-label">เวลาประชุม</label>
      <div class="col-sm-10">
				<div class="input-group">
					<div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
					<input type="text" name="time" readonly="true" class="form-control pull-right" id="time" />
				</div>
      </div>
    </div>
		<div class="form-group">
      <label for="title" class="col-sm-2 control-label">ผู้เกี่ยวข้อง</label>
      <div class="col-sm-10">
				<select name="ref[]" data-placeholder="เลือกผู้เกี่ยวข้อง" class="chzn-select form-control" multiple required>
					<optgroup label="BOSS">
				<?php $last=1;foreach($this->people as $k=>$v):?>
					<?php if($v['code']>100 && $v['code']<=300 && $last!=2):$last=2;?>
					</optgroup><optgroup label="Jarm.com - iNet Revolutions">
					<?php elseif($v['code']>300 && $v['code']<=500 && $last!=3):$last=3;?>
					</optgroup><optgroup label="Jarm.com - ฝึกงาน">
					<?php elseif($v['code']>500 && $v['code']<=700 && $last!=4):$last=4;?>
					</optgroup><optgroup label="BoxzaRacing - Racing Box">
					<?php elseif($v['code']>700 && $v['code']<=900 && $last!=5):$last=5;?>
					</optgroup><optgroup label="BoxzaRacing - ฝึกงาน">
					<?php endif?>
					<option value="<?php echo $k?>"><?php echo $v['nickname'].' - '.$v['th']['first'].' '.$v['th']['last']?></option>
				<?php endforeach?>
					</optgroup>
				</select>
      </div>
    </div>
    <div class="form-group">
      <label for="detail" class="col-sm-2 control-label">เนื้อหา</label>
      <div class="col-sm-10">
        <textarea class="mceEditor" id="detail" name="detail" style="height:700px; width:630px;" minlength="20"><?php echo htmlspecialchars($this->content['detail'],ENT_QUOTES,"UTF-8")?></textarea>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-2 control-label"></label>
      <div class="col-sm-10">
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </div>
  </form>
</div>
<script>
_.mce={upload:'/announce/upload'};
$(function() {
  tinymce.remove('textarea');
	tinymce.init({
    selector: "textarea",
		plugins: [
		"advlist autolink lists link image print preview anchor",
		"searchreplace visualblocks code fullscreen table",
		"media table contextmenu paste textcolor colorpicker",
		"upload"
		],
		toolbar: "code | bold italic underline | forecolor backcolor | bullist numlist | alignleft aligncenter alignright | link media | table",

    menubar:false,
    statusbar: false,
    paste_as_text: true,
		paste_remove_spans : true,
		paste_remove_styles : true,
		paste_create_paragraphs : true,
		paste_create_linebreaks : false,
		paste_force_cleanup_wordpaste : true,
		paste_auto_cleanup_on_paste : true,
		paste_convert_middot_lists : false,
		paste_strip_class_attributes : "all",
		paste_convert_headers_to_strong : true,
		paste_text_linebreaktype : "p",
		paste_word_valid_elements: "strong",
		paste_preprocess : function(pl, o) {
						o.content = o.content
								.replace(/<div(.*?)>(.*?)<\/div>/gi,'<p$1>$2</p>')
								.replace(/(.*?)<br\s?\/?>/gi,'<p>$1</p>')
		},
		invalid_styles: 'font-size font-family',
		keep_styles: false,
		extended_valid_elements : "a[href|title|target=_blank|rel=nofollow]",
		invalid_elements : "br,pre,div",
		forced_root_block : 'p',
		force_p_newlines : true,
		force_br_newlines : false,
		convert_newlines_to_brs : false,
		remove_linebreaks : true,
		convert_urls : true,
		relative_urls : false,
		remove_script_host : false,
	});
});

$(function () {
	$('#time').daterangepicker({
		singleDatePicker:true,
		showDropdowns:true,
		timePicker: true,
		timePickerIncrement: 30,
		opens: 'right',
    locale: {
        format: 'YYYY-MM-DD HH:mm:ss'
    }
	});

	var config = {
		 '.chzn-select'           : {},
		 '.chzn-select-deselect'  : {allow_single_deselect:true},
		 '.chzn-select-no-single' : {disable_search_threshold:10},
		 '.chzn-select-no-results': {no_results_text:'Oops, nothing found!'},
		 '.chzn-select-width'     : {width:"95%"}
	 }
	 for (var selector in config) {
		 $(selector).chosen(config[selector]);
	 }
});
</script>
