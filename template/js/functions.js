var $j = jQuery.noConflict();

$j(window).load(function(){
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

function showHideFilters(){
	$j('#filters').toggle();
}

function animateMenu(obj) {
    obj.classList.toggle("change");
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

function remove_filter(id) {
    // remove hidden field
    $j("#"+id).remove();
    var filter = '';

    $j('.apply_filter').each(function(i){
        filter += this.value + ' AND ';
    });
    // remove last AND of string
    filter = filter.replace(/\sAND\s$/, "");

    $j('#filter').val(filter);
    $j("#formFilters").submit();
}
function show_similar(url){
    //$j("#ajax").load(url);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
           document.getElementById("ajax").innerHTML = this.responseText;
        }else {
           document.getElementById("ajax").innerHTML = '<li class="cat-item"><div class="loader"></div></li>';
        }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}
function tabs(tab) {
    $j('.abstract-version').hide();
    $j('#tab-'+tab).show();
    $j('li').removeClass('active');
    $j('li').click(function(){
        $j(this).addClass('active');
    });
}
