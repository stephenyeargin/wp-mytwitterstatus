<?php
/*
Plugin Name: My Twitter Status
Plugin URI: http://stephenyeargin.com/blog/tag/plugins/
Description: Retrieve a user's latest Twitter status
Author: Stephen Yeargin
Version: 0.1
Author URI: http://stephenyeargin.com/
*/

/**
 * MyTwitterStatus Class
 */
class MyTwitterStatus {
	var $config;
	
	/**
	 * Constructor
	 */
	function MyTwitterStatus($username='',$uuid=0,$count=1) {	
		$this->config = array();
		$this->config['username'] = (string) $username;
		$this->config['uuid'] = (int) $uuid;	
		$this->config['count'] = (int) $count;
	}
	
	/**
	 * Retrieves the Twitter statuses
	 *
	 * @return	array	Array of statuses
	 */
	function getTweets() {
		$url = 'http://twitter.com/statuses/user_timeline/' . $this->config['uuid'] . '.rss';
		$rss = fetch_feed($url);
		if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
		    $maxitems = $rss->get_item_quantity($this->config['count']); 
		    $rss_items = $rss->get_items(0, $maxitems);
			return $rss_items;
		endif;
	}
	
	/**
	 * Output Twitter statuses
	 *
	 * @return	print	Output
	 */
	function showTweets() {
		$items = $this->getTweets();
		if (count($items) == 0) {
			$this->showTwitterError();
		} else {
		    foreach ($items as $item ) {
				$text  = $this->formatTweets($item->get_title());
		    	$url   = $item->get_link();
		    	$date  = $this->formatTime($item->get_date());
		    	print "\t<li class=\"twitterStatus\">$text</li>\n";
		    	print "\t<li class=\"twitterTimestamp\"><a href=\"$url\">$date</a></li>\n";
		    }
		}
	}
	
	/**
	 * Add links for URLs, @replies and #hashtags
	 *
	 * @param	string	Text to format
	 * @return	string	Formatted HTML
	 */
	function formatTweets($text) {
		$trim = (int) strlen($this->config['username'])+2;
		$text = substr($text, $trim); // trim username
		$text = $this->filter_hyperlink($text); // fix links
		$text = preg_replace("#(^|[\n ])@([^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://www.twitter.com/\\2\" >@\\2</a>'", $text); // @replies
		$text = preg_replace("#(^|[\n ])\#([^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://search.twitter.com/search?q=%23\\2\" >#\\2</a>'", $text); // #hashtags
		return $text;
	}
	
	/**
	 * Show a time since for a given date
	 *
	 * @param	string	Timestamp as a string
	 * @return	string	Calculated time since
	 * @url		http://www.php.net/manual/en/function.time.php#89415
	 */
	function formatTime($date)
	{
	    if (empty($date)) {
	        return "No date provided";
	    }
	    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	    $lengths         = array("60","60","24","7","4.35","12","10");
	    $now             = time();
	    $unix_date       = strtotime($date);
	    // check validity of date
	    if(empty($unix_date)) {   
	        return "Bad date";
	    }
	    // is it future date or past date
	    if($now > $unix_date) {   
	        $difference     = $now - $unix_date;
	        $tense         = "ago";
	    } else {
	        $difference     = $unix_date - $now;
	        $tense         = "from now";
	    }
	    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	        $difference /= $lengths[$j];
	    }
	    $difference = round($difference);
	    if($difference != 1) {
	        $periods[$j].= "s";
	    }
	    return "$difference $periods[$j] {$tense}";
	}


	/**
	 * Auto convert hyperlinks in text
	 *
	 * @param   string  Text to filter
	 * @return  string  Filtered text
	 * @url     http://www.php.net/manual/en/function.preg-replace.php#77787
	 */
	function filter_hyperlink($text) {
	    $text=preg_replace("/(http:\/\/|www|[a-zA-Z0-9-]+\.|[a-zA-Z0-9\.-]+@)(([a-zA-Z0-9-][a-zA-Z0-9-]+\.)+[a-zA-Z0-9-\.\/\_\?\%\#\&\=\;\~\!\(\)]+)/","<a href=\"http://\\1\\2\">\\1\\2</a>",$text);

	    // Remove double protocols
	    $text = str_replace('<a href="http://http://', '<a href="http://', $text);

	    return $text;
	}

	/**
	 * Show Twitter Error (Can be overridden)
	 */
	function showTwitterError() {
		print "\t<li class=\"twitterStatus\"><em>Wondering why Twitter stopped updating my website ...</em></li>";
	    print "\t<li class=\"twitterTimestamp\"><a href=\"#\">Not Cool.</a></li>\n";
	}
	
}

/**
 * Usage Function
 */
function MyTwitterStatus($username='',$uuid=0,$count=1) {

	$tweets = new MyTwitterStatus($username,$uuid,$count);
	$tweets->showTweets();

}
	