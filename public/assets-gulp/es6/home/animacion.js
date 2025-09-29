$('.acordion').mouseover(function () {
	$('.acordion').removeClass('shadow');
	$('.acordion').removeClass('acordion-active');
	$(this).addClass('shadow');
	$(this).addClass('acordion-active');
	$(this).find('.collapse').collapse('show');

	var id = $(this).attr("data-id");
	$(".presentacion-body .item_presentacion").removeClass("active");
	$("#img_" + id).addClass("active");
	var vid = document.getElementById("video_" + id);
	if (vid) {
		vid.play();
	}
});

$(".acordion").on("click", function () {
    // Get some values from elements on the page:
    $(".acordion").removeClass("shadow");
    $(this).addClass("shadow");

});

$(".acordion").mouseover();