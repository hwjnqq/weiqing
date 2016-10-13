laytpl.config({open: '<{', 'close': '}>'});

$.toast = function(msg, url, time) {
	var $toast = $("<div class='modal toast'>"+msg+"</div>").appendTo(document.body);
	$.openModal($toast);
	setTimeout(function() {
		$.closeModal($toast);
		if(url) {
			$.router.loadPage(url);
			return false;
		}
	}, time || 2000);
};

$.icloseModal = function(modal, notRemove){
	if(notRemove) {
		$('.modal-overlay').removeClass('modal-overlay-visible');
		$(modal).removeClass('modal-in').addClass('modal-out').transitionEnd(function(e) {
			$(modal).removeClass('modal-out');
			$(modal).hide();
		});
		return true;
	}
	$.closeModal(modal);
	return true;
};

var timeStamp = new Date().getTime();