<?php
/**
 * IP.Board Comments - a WordPress plugin
 * 
 * Todo:
 * update value for $post->comment_count in WordPress in some regular interval
 * convert html to bbcode for the SetPostContent()
 */

class WP_IPB {

  function add_topic ($post_ID)  
  {
        // manual: enter the user id # that will create the new topics
        $memberID = 1; // Admin user

        // http://codex.wordpress.org/Function_Reference/get_post
        $wp = get_post($post_ID);

        // manual: create an array of allowed forums to match against
        // in the format 'Forum Name' => forum_id
        // your wordpress category names need to match these
        $ipb_categories = array(
                'Computers'=>1, 
                'Games'=>2,
                'Movies'=>3,
                'Photos'=>4
                );

        foreach (get_the_category($wp->ID) as $cat)
        {
                $cat_name = html_entity_decode($cat->name);

                // cat_ID, cat_name, category_nicename, category_description, category_parent, category_count
                if (array_key_exists($cat_name,$ipb_categories))
                {
                        $forumID = $ipb_categories[$cat_name];
                        break;
                }

        }

        // we haven't found a matching category, do nothing
        if (! isset($forumID)) return FALSE;

        // manual: enter the path to your forum's initdata.php
        require_once( '/path/to/initdata.php' );

        require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );
        require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );

        $registry = ipsRegistry::instance();
        $registry->init();

        require_once( IPSLib::getAppDir( 'forums' ) . '/app_class_forums.php' );
        $appClass    = new app_class_forums( $registry );

        require_once( IPSLib::getAppDir('forums') . '/sources/classes/post/classPost.php' );
        $postClass = new classPost( $registry );

        $postClass->setForumID( $forumID );
        $postClass->setForumData( $registry->class_forums->allForums[ $forumID ] );

        $postClass->setIsPreview( FALSE );

        $postClass->setAuthor( $memberID );
        $postClass->setTopicTitle( $wp->post_title );

        // maybe use $wp->post_name slug instead of guid
        $content = $wp->post_content."\n\n".'[url="'.$wp->guid.'"]Read the full story here[/url]';

		$postClass->setPostContent( $content );
		$postClass->setSettings( array( 
			'enableSignature' => 0,
		    'enableEmoticons' => 0,
		    'post_htmlstatus' => 2,
		    'enableTracker'   => 0
		    ) );

        $postClass->setTopicState('open');
        $postClass->setPublished( TRUE );

        try
        {
            if ($postClass->addTopic())
            {
                        $topicData = $postClass->getTopicData();
                        // add custom fields to our post
                        // manual: add your own forum url here
                        $topicUrl = sprintf("http://yourforum.com/topic/%s-%s",$topicData['tid'],$topicData['title_seo']);
                        update_post_meta($wp->ID,'forum_topic_url',htmlentities($topicUrl));
            }
            else
            {
                        var_dump($postClass->_postErrors);
                        var_dump($content);
            }
        }
        catch( Exception $error )
        {
            print $error->getMessage();
        }

  }

}

// use post publish status transitions to ensure this only posts in case of a new file
// and not whenever a post is edited or updated
add_action('new_to_publish', array('WP_IPB', 'add_topic'));
add_action('future_to_publish', array('WP_IPB', 'add_topic'));
add_action('draft_to_publish', array('WP_IPB', 'add_topic'));


/**
 * add topic url to end of post
 */
function add_ipb_topic_url ($content)
{
        $meta = get_post_custom_values('forum_topic_url');
        
        if (empty($meta)) return $content;

        $url = sprintf('<p class="discussion"><a href="%s">Follow the Discussion in Progress</a></p>',
                current($meta));
        return $content.$url;
}

add_filter('the_content', 'add_ipb_topic_url');

