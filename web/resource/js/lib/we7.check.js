/*
 * 单选多选插件
 * 自己写的 待完善
 */
(function($){
	$.fn.we7_check = function(){
		var _this = $(this);
		function markChenck() {
			_this.each(function(){
				var $this = $(this);
				var $this_type = $this.attr('type');
				var $this_class  = $this.attr('class') ? ' ' +  $this.attr('class') :'';
				var $this_style = $this.attr('style') ? 'style="' + $this.attr('style') + '"' :'';
				var $this_option = $this.prev('.we7-option').length;
				var $this_label = $this.parent('label').html();
				var $this_name = $this.attr('name');
				var isCheck = ($this.is(':checked') || $this.attr('we7-check-checked')) ? ' checked ' : '';
				var isDisabled = $this.is(':disabled') ? ' disabled ' : '';
				var isCheckAll = $this.attr('we7-check-all') || $this_type == 'radio';
				if(!$this_option) {
					$this.before('<div class="we7-option we7-'+$this_type+'-option'+ isCheck + isDisabled + $this_class +'"' + $this_style + '></div>');
					$this.on('click',function(){
						checked(isCheckAll,$this_name,$this);
					});
					$this.on('change',function(){
						checked(isCheckAll,$this_name,$this);
					})
				}
				if(!$this_label) {
					$this.prev('.we7-option:not(".disable")').on('click',function(){
						var option_checked = $(this).hasClass('checked');
						var input_checked = $(this).next(':radio,:checkbox').is(':checked');
						if(option_checked == input_checked) {
							$this.trigger("click");
							if($this_type == 'radio') {
								$this.trigger("click");
							} 
						} else {
							$(this).toggleClass('checked');
						}
						
					});
				}
			});
		}
		function checked(isCheckAll,name,$this) {
			if(isCheckAll){
				$('body').find('[name="' + name + '"]').each(function(){
					if($(this).is(':checked')){
						$(this).prev('.we7-option').addClass('checked');
					} else {
						$(this).prev('.we7-option').removeClass('checked');
					}
				});
			} else {
				if($this.is(':checked')){
					$this.prev('.we7-option').addClass('checked');
				} else {
					$this.prev('.we7-option').removeClass('checked');
				}
			}
			
		}
		markChenck();
	}
 })(jQuery);