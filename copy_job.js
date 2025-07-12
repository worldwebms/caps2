function copyJob() {
				console.log('----------');
				var params = {}
				params['job_id'] = <?php $job_id ?>;
				params['client_id'] = $('#client-id').val();
						console.log(params);
				jQuery.post(
						'copy_job.php',
						params,
						function( response ) {
							alert( "success" );
						},
						'json'
					);
			}
