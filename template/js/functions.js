var $j = jQuery;

$j(window).load(function(){
	regExp();
	reportarErro();
	sugerirTag();
	showTips();
	show_more_list();
});

function change_count(elem) {
    var form = document.searchForm;
    form.count.value = elem.value;
    $j("#searchForm").submit();
}

function change_format(elem) {
    var form = document.searchForm;
    form.format.value = elem.value;
    $j("#searchForm").submit();
}

function change_sort(obj){
    var sort = obj.options[obj.selectedIndex].value;
    var form = document.searchForm;
    form.sort.value = sort;
    $j("#searchForm").submit();
}

function showTips(){
	$j('.tooltip').tooltipster({
		animation: 'fade',
	});
}

function show_more_list(){
	$j('.more-items a').click(function() {
		var element = $j(this).parent().prev().children('.hide');
		if ( element.length ) {
			element.each(function( index ) {
				if ( index < 5 ) {
  				$j(this).removeClass('hide');
        }
        else {
          return false;
        }
			});

      var el = $j(this).parent().prev().children('.hide');

      if ( !el.length ) {
        $j(this).parent().hide();
      }
		}
	});
}

function regExp(){
	if($j(".cat-item").length){
		$j("#categories-3 .cat-item").each(function(e){
			i = e+1;
			element = $(this);

			var cat_text = element.html();
			var cat_link = element.children("a").attr("href");
			var cat_nome = element.children("a").text();
			var cat_new  = "<a href='"+cat_link+"' title='Ver todos os posts arquivados em "+cat_nome+"'>"+cat_nome+"</a>";

			var regex    = /(.*)(\()(.*)(\))/;
			var result   = cat_text.replace(regex, "<span class='cat-item-count'>$3</span>");
			element.text("").append(cat_new+result);
		});
	}
}

function reportarErro(){
	$j(".reportar-erro-open").on("click", function(){
        $j(".reportar-erro").hide();
		$j(".compartilhar").hide();
		$j(".sugerir-tag").hide();
        $j(".error-report-result").hide();
		$j(this).siblings(".reportar-erro").show();
        $j(".erro-form").show();
	});

	$j(".reportar-erro-close").on("click", function(){
		$j(".reportar-erro").hide();
	});

    // Attach a submit handler to the form
    $j( "#reportErrorForm" ).submit(function( event ) {

      // Stop form from submitting normally
      event.preventDefault();

      // Get some values from elements on the page:
      var $form = $j( this ),
        url = $form.attr( "action" );

      // Send the data using post
      var posting = $j.post( url, $form.serialize() );

      // Put the results in a div
      posting.done(function( data ) {
        $j(".erro-form").hide();

        if (data == 'True'){
            $j(".error-report-result").find('#result-problem').hide();
        }else{
            $j(".error-report-result").find('#result-ok').hide();
        }
        $j(".error-report-result").show();

      });
    });

}

function sugerirTag(){
	$j(".sugerir-tag-open").on("click", function(){
		$j(".reportar-erro").hide();
		$j(".compartilhar").hide();
		$j(".sugerir-tag").hide();
        $j(".sugerir-tag-result").hide();
		$j(this).siblings(".sugerir-tag").show();
        $j(".sugerir-form").show();
	});

	$j(".sugerir-tag-close").on("click", function(){
		$j(".sugerir-tag").hide();
	});


    // Attach a submit handler to the form
    $j( "#tagForm" ).submit(function( event ) {

      // Stop form from submitting normally
      event.preventDefault();

      // Get some values from elements on the page:
      var $form = $j( this ),
        resource_id = $form.find( "input[name='resource_id']" ).val(),
        tags = $form.find( "input[name='txtTag']" ).val(),
        url = $form.attr( "action" );

      // Send the data using post
      var posting = $j.post( url, { resource_id: resource_id, tags: tags } );

      // Put the results in a div
      posting.done(function( data ) {
        $j(".sugerir-form").hide();

        if (data == 'True'){
            $j(".sugerir-tag-result").find('#result-problem').hide();
        }else{
            $j(".sugerir-tag-result").find('#result-ok').hide();
        }
        $j(".sugerir-tag-result").show();

      });
    });
}
