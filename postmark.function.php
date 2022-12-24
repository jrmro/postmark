<?php

/**
* Lightweight function that abstracts sending an html email using Postmark's REST API via cURL:
* https://postmarkapp.com/developer/api/overview
* https://postmarkapp.com/developer/api/email-api
* https://postmarkapp.com/developer/user-guide/send-email-with-api
*
* Email addresses can be expressed as `email@example.com` or `Name <email@example.com>`
*
* Example Usage:
*
* $postmark = postmark([
* 	'api_key' => 'POSTMARK_API_KEY', 
* 	'to' => ['jane.doe@example.com', 'John Doe <john.doe@example.com>'],
* 	'cc' => ['George Washington <g.washington@example.com>', 'abraham.lincoln@example.com'], // Optional
* 	'bcc' => ['Alexander Hamilton <a.hamilton@example.com>'], // Optional
* 	'from' => 'sender@example.com',
* 	'reply_to' => 'sender@example.com', // Optional. Defaults to the Reply To set in the sender signature.
* 	'subject' => 'My First Email',
* 	'content' => 'Hello <strong>World</strong>!',
* 	'attachments' => array( // Optional	
* 		[ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment1.txt' ], 
* 		[ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment2.txt' ], 
* 		[ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment3.txt' ], 
* 	)
* ]);
*
* @author     Joseph Romero
* @version    1.0
* ...
*/

if ( ! function_exists('postmark'))
{
	
	function postmark($params = array(

		'api_key' => null, 
		'to' => [], 
		'cc' => [], // Optional
		'bcc' => [], // Optional
		'from' => null, 
		'reply_to' => null, // Optional. Defaults to the Reply To set in the sender signature.
		'subject' => null,
		'content' => null,
		'attachments' => array( // Optional 
			[ 'content' => null, 'type' => null, 'filename' => null ]
		 )
		 
	))
	{

		// Check for required params

			$missing_params = array();
				
			foreach(['api_key', 'to', 'from', 'subject', 'content'] as $required)
			{
				if(empty($params[$required])) $missing_params[] = $required;
			}
				
			if( ! empty($missing_params)) return array('error' => true, 'message' => 'The following required parameters are missing: ' . implode(', ', $missing_params));


		// Parse Recipients

			foreach(['to', 'cc', 'bcc'] as $recipients) {

				if( isset($params[$recipients]) ){

					if( ! is_array($params[$recipients]) ) return array('error' => true, 'message' => "'{$recipients}' email address(es) must be specified in an array.");

					// Convert to string. Multiple addresses are comma-separated.
					$params[$recipients] = implode(',', $params[$recipients]);

				}

			}
			

		// Send to Postmark endpoint using cURL

			$fields = array(
				'From' => $params['from'],
				'To' => $params['to'],
				'Cc' => ! empty($params['cc']) ? $params['cc'] : '',
				'Bcc' => ! empty($params['bcc']) ? $params['bcc'] : '',
				'Subject' => $params['subject'],
				'HtmlBody' => $params['content'],
			);

			if( ! empty($params['reply_to'])) $fields['ReplyTo'] = $params['reply_to'];


			// Set attachments
		
			$fields['Attachments'] = array(); // Reset placeholder attachments in template

			if( isset($params['attachments']) )
			{

				if( ! is_array($params['attachments']) ) return array('error' => true, 'message' => 'Attachments must be included in an array.');

				foreach($params['attachments'] as $attachment)
				{

					if( empty($attachment['content']) || empty($attachment['type']) || empty($attachment['filename']) ) return array('error' => true, 'message' => "Each attachment must be an array that includes values for 'content', 'type', and 'filename'.");

					$fields['attachments'][] = array(
						'Name' => $attachment['filename'],
						'Content' => $attachment['content'],
						'ContentType' => $attachment['type']
					);

				}

			} 

			if(empty($fields['Attachments'])) unset($fields['Attachments']); // Purge placeholder attachments (rather than submit empty array). 
			 

			// Send to Postmark endpoint using cURL 
			
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://api.postmarkapp.com/email',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($fields),
				CURLOPT_HTTPHEADER => array(
					'Accept: application/json',
					'Content-Type: application/json',
					"X-Postmark-Server-Token: {$params['api_key']}"
				),
				CURLOPT_SSL_VERIFYPEER => FALSE,
			));
			
			$response = curl_exec($curl);
			
			curl_close($curl);


		// Output Response	
		
			// cURL Failed
			if($response === false) return array('error' => true, 'message' => 'cURL failed.');		
			
			// Success
			$response = json_decode($response, true);
			if( isset($response['ErrorCode']) && $response['ErrorCode'] == 0 ) return array('error' => false, 'message' => 'Success!');
			
			// Postmark Error
			return array('error' => true, 'message' => isset($response['Message']) ? $response['Message'] : 'Something went wrong.');

	}

}
