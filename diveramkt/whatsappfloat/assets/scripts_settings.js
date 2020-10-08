// $('#wrap_Inputfield_teasers ul.Inputfields.ui-sortable > li').addClass("InputfieldStateCollapsed");
// $('[id^=wrap_Inputfield_repetidor_] ul.Inputfields.ui-sortable > li').addClass("InputfieldStateCollapsed");
$('document').ready(function(){

	if($('#Repeater-formLinksPersonalizados-items-links_personalizados li.field-repeater-item').length){
		$('#Repeater-formLinksPersonalizados-items-links_personalizados li.field-repeater-item').addClass('collapsed');
		$('#Repeater-formLinksPersonalizados-items-links_personalizados li.field-repeater-item').each(function(){
			$(this).find('.repeater-item-collapsed-title').html($(this).find('div[data-field-name="title"] input').val());
		})
	}

})