$().ready(function(){	
	
	
	
	// Click sur une checkbox TOOLS
	$("#section_tools input").click(function(){
		// Si cochage d'un tool
		if($(this).is('input:checked'))
		{
			// Récup des types associés au tool
			var types = $(this).data('types');
			
			// pour chaque type associé
			$.each(types,function(key,val){
				var type_exist = false;
			
				// pour chaque type existant
				$('#section_folders input[type="checkbox"]').each(function(){
					
					// si type associé = type existant
					if(val == $(this).data('name'))
					{
						// on coche le type existant
						$(this).prop('checked', true);
						type_exist = true;	
					}
				});
				
				// s'il n'y a pas de case à cocher, on la rajoute en custom
				if(!type_exist) {
					var e = $("#input_folders_others");
					var actual_val = e.val();
					if(actual_val != "") val = ','+val;
					 
					$("#input_folders_others").val(actual_val+val);
				}
			});
			
		}
	});

	// demande de génération
	$('#btn_valid').click(function(){
	
		if($("#input_prj_name").val() == "")
		{
			$("#input_prj_name").parent().addClass('has-error');
			return;
		}
		else
		{
			$("#input_prj_name").parent().removeClass('has-error');
		}
	
		// récup des données de propriétés
		var datas = {
			'properties' : {
				'name':$("#input_prj_name").val(),
				'title':$("#input_page_title").val(),
				'main_type': $("#sel_mainFile").val()
			},
			'folders' : [],
			'tools' : []		
		}
		
		// récup des données de folders (checkbox)
		$("#section_folders input:checked").each(function(i){
			datas.folders.push( $(this).data('name') );
		});
		
		// Récup des données de folders (input text)
		$.each($('#input_folders_others').val().split(/[,;]+/), function(key, val){
			if (val != '')
			{
				datas.folders.push(val);
			}
		});
		
		// récup des données de tools
		$("#section_tools input:checked").each(function(i){
			datas.tools.push( $(this).data('name') );
		});
		
		$(this).attr('disabled','disabled');
		$("#btn_valid span").removeClass('glyphicon-arrow-down');
		$("#btn_valid span").addClass('glyphicon-refresh');
		
		// envoi des données via AJAX
		$.getJSON('generate.php',{'generate_datas':datas}, function(ret) {
			if(ret.status == 'ok')
			{
				$("#download_frame").attr('src',ret.msg);
			}
			else
			{
				alert("Erreur");
			}
			
			
			$("#btn_valid").removeAttr("disabled");
			$("#btn_valid span").removeClass('glyphicon-refresh');
			$("#btn_valid span").addClass('glyphicon-arrow-down');
		});
		
		
		
	});
})