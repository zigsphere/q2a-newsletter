<?php
	function qa_get_request_content() {
		if(qa_opt('news_plugin_active')) {
			$requestlower=strtolower(qa_request());
			if($requestlower && $requestlower === 'my-profile') {
				$userid = qa_get_logged_in_userid();
				if(!$userid)
					qa_redirect('login');

				$handles = qa_userids_to_handles(array($userid));
				$handle = $handles[$userid];
				qa_redirect(qa_path('user/'.$handle));
			}
			else if($requestlower && $requestlower === qa_opt('news_plugin_request')) {
				if(qa_opt('news_plugin_static')) {

					// refresh
					
					if(qa_opt('news_plugin_send') && ((qa_opt('news_plugin_send_time') && (int)qa_opt('news_plugin_send_days')) || (qa_get('cron') == qa_opt('news_plugin_cron_rand') && qa_opt('news_plugin_send_cron'))) && time() >= qa_opt('news_plugin_send_last')+(23*60*60)) { // minumum cron interval is 23 hours
						qa_news_plugin_createNewsletter();
						if(qa_get('cron') == qa_opt('news_plugin_cron_rand')) {
							echo "true\n";
							return false;
						}
					}
					else if (qa_get('cron') == qa_opt('news_plugin_cron_rand')) {
						if(!qa_opt('news_plugin_send_cron'))
							error_log('Q2A Newsletter Recreate Error: cron request not allowed via admin/plugins');
						else
							error_log('Q2A Newsletter Recreate Error: cron request before minimum time elapsed');
						
						echo "false\n";
						return false;
					}

					include(qa_opt('news_plugin_loc'));
				}
				else
					echo qa_news_plugin_createNewsletter(true);
				return false;
			}
			else if(qa_opt('news_plugin_pdf') && $requestlower && $requestlower === qa_opt('news_plugin_request_pdf')) {
				if(qa_opt('news_plugin_static')) {
					
					// refresh
					
					if(qa_opt('news_plugin_send') && (qa_opt('news_plugin_send_time') && (int)qa_opt('news_plugin_send_days')) && time() > qa_opt('news_plugin_send_last')+(qa_opt('news_plugin_send_days')*60*60)) {
						qa_news_plugin_createNewsletter();
					}
						
					$pdf = file_get_contents(qa_opt('news_plugin_loc_pdf'));
					header('Content-Description: File Transfer');
					header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
					header('Pragma: public');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
					header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
					// force download dialog
					header('Content-Type: application/force-download');
					header('Content-Type: application/octet-stream', false);
					header('Content-Type: application/download', false);
					header('Content-Type: application/pdf', false);
					// use the Content-Disposition header to supply a recommended filename
					header('Content-Disposition: attachment; filename="'.basename(qa_opt('news_plugin_loc_pdf')).'";');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: '.strlen($pdf));
					echo $pdf;
				}
				else
					qa_news_plugin_create_pdf(true);
				return false;
			}
		}
		return qa_get_request_content_base();
	}
