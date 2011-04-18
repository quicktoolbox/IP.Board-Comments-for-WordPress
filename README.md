IP.Board Comments for WordPress
-------------------------------
Please fork and update the code with suggested improvements.

You'll need to edit the code at this stage, because it isn't fully integrated into WordPress dashboard for customizing the settings.  Look for the "manual: " prefix inside the code to indicate where you'll need to edit something.  Currently, those locations are:

#1. Member ID for the user who will be creating new topics
`// manual: enter the user id # that will create the new topics
$memberID = 1; // Admin user`

#2. an array of $ipb_categories, containing the exact names of your forum categories, and corresponding forum ID #'s
`// manual: create an array of allowed forums to match against
// in the format 'Forum Name' => forum_id
// your wordpress category names need to match these
$ipb_categories = array(
	'Computers'=>1, 
	'Games'=>2,
	'Movies'=>3,
	'Photos'=>4
	);`

#3. path to your IP.Board installation initdata.php
`// manual: enter the path to your forum's initdata.php
require_once( '/path/to/initdata.php' );`

#4. the url to your IP.Board installation
`// manual: add your own forum url here
$topicUrl = sprintf("http://yourforum.com/topic/%s-%s",$topicData['tid'],$topicData['title_seo']);
update_post_meta($wp->ID,'forum_topic_url',htmlentities($topicUrl));`
	
