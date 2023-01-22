# postmark

Lightweight function that abstracts sending an html email using Postmark's REST API via cURL:

* [https://postmarkapp.com/developer/api/overview](https://postmarkapp.com/developer/api/overview)
* [https://postmarkapp.com/developer/api/email-api](https://postmarkapp.com/developer/api/email-api)
* [https://postmarkapp.com/developer/user-guide/send-email-with-api](https://postmarkapp.com/developer/user-guide/send-email-with-api)

## Sample Usage

Email addresses can be expressed as `john.doe@example.com` or `John Doe <john.doe@example.com>`.

```
$postmark = postmark([
    'api_key' => 'POSTMARK_API_KEY', 
    'to' => ['jane.doe@example.com', 'John Doe <john.doe@example.com>'],
    'cc' => ['George Washington <g.washington@example.com>', 'abraham.lincoln@example.com'], // Optional
    'bcc' => ['Alexander Hamilton <a.hamilton@example.com>'], // Optional
    'from' => 'sender@example.com',
    'reply_to' => 'sender@example.com', // Optional. Defaults to the `Reply To` set in the sender signature.
    'subject' => 'My First Email',
    'content' => 'Hello <strong>World</strong>!',
    'attachments' => array( // Optional	
        [ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment1.txt' ], 
        [ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment2.txt' ], 
        [ 'content' => 'BASE64_ENCODED_CONTENT', 'type' => 'text/plain', 'filename' => 'attachment3.txt' ], 
    )
]);
```
## Return Values
Function returns an associative array with the following values: 'error' (bool) , 'message' (string), 'status' (int). Upon success, 'error' will be false (bool). HTTP status code is assigned to 'status'.
