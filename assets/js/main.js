$(function () {
    const config = $('body').data('config');
    console.log(config);
    $(document).pjax('.pjax-box a', '#pjax', {
		scrollTo: 235,
		timeout: 2000
	});
});
