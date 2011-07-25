<?php

/**
 * @file
 * @author  Mark Elo 
 * @version 1.0
 *
 * @section LICENSE 
 * The Picasa Albums Class
 * 
 * This returns Picasa Web Albums data. As we are not modifying any data OAuth is not needed, only the desired Google 
 * Username is required
 * 
 * By Mark Elo (http://www.calphpp.com)
 * July 24, 2011
 *
 * Example:
  *
 * $user = your gmail login: your.name@google.com
 * $picasa = new picasa($user); 
 * foreach($picasa->getAlbumTitleAndLinks() as $albumName => $albumLink)
 *	   print "<a href='".$albumLink."'>".$albumName."</a><br />";
 * 
 * Methods and Usage:
 *
 * $picasa->getTitle(); // Returns Picasa overall album name
 * Usage: print $picasa->getTitle();
 * $picasa->getIcon() // Retruns path to profile image.
 * Usage: print '<img src="'.$picasa->getIcon().'">';
 * $picasa->getUpDated() // Returns album last update dateTime
 * Usage: print $picasa->getUpDated();
 * $picasa->getRSSLink() // Retuns RSS feed URL
 * Usage: print '<a href="'.$picasa->getRSSLink().'">RSS</a>';
 * $picasa->getAlbumLink() // Returns Link to overall album
 * Usage: <a href="'.$picasa->getAlbumLink().'">Album</a>
 * $picasa->getFlashLink()) // Returns Link to overall flash show
 * Usage: <a href="'.$picasa->getFlashLink().'">Flash</a>' 
 * $picasa->getAuthor() // Returns Author
 * Usage: print $picasa->getAuthor();
 * print $picasa->getUri() // Get URI
 * Usage: print $picasa->getUri();
 * $picasa->getAlbumTitles() // Returns an array containg all the albums
 * Usage: foreach($picasa->getAlbumTitles() as $value)
 *		      print $value."<br />";
 * $picasa->getAlbumLinks() // Reurns an array conating all the URL's to eack album
 * Usage: foreach($picasa->getAlbumLinks() as $value)
 *			  print $value."<br />";
 * $picasa->getAlbumTitleAndLinks() // returns a Key/value array of Album names and URL's
 * Usage: foreach($picasa->getAlbumTitleAndLinks() as $albumName => $albumLink)
		      print "<a href='".$albumLink."'>".$albumName."</a><br />";
 *
 *
 */

 
/**
 * \brief utilitity classes
 */
abstract class simplePicasa {

	function __construct(){ } 

}

/**
 * \brief container stub for Albums names and Links.
 */
class albumTitlesAndLinks extends simplePicasa{

}

/**
 * \brief returns phpinfo string
 */
class picasa extends simplePicasa {  

	private $album_feed;
	private $updated;
	private $icon;
	private $title;
	private $albumTitle;
	private $rssLink;
	private $flashLink;
	private $ablumLink;
	private $author;
	private $uri;
	private $type;
	private $albumTitlesAndLinksArray;
	private $albumLinksArrayObj;
	private $albumTitlesArrayObj;
	
    function __construct($user){ 
	$this->albumTitlesAndLinksArray = new ArrayObject(new albumTitlesAndLinks());
	$this->albumLinksArrayObj = new ArrayObject($albumLinks = array());
	$this->albumTitlesArrayObj = new ArrayObject($albumTitles = array());
	$album_feed = 'http://picasaweb.google.com/data/feed/api/user/' . $user . '?v=2';
	$xml = simplexml_load_file($album_feed);
	foreach($xml as $xmlns => $value){ // iterates over whole array
		$nextLevel = $this->parsTopLevel($xmlns, $value);
		$this->parsNextLevel($value, $nextLevel);
		$this->parsLastLevel($value, $nextLevel);
		}
	
	}
	
	/**
	* pars top level of NS, return top level set top level values, or return there is a next leval value pending.
	*/
	private function parsTopLevel($xmlns, $value) {
		switch ($xmlns) {
			case "updated":
				$this->updated = $value;
				break;
			case "title":
				$this->title = $value;
				break;
			case "icon":
				$this->icon = $value;
				break;
			case "link":
				return "linkFound";
				break;
			case "author":
				return "authorFound";
				break;
			case "entry":
				return "entryFound";
				break;
		}
	}
	
	/**
	* pars next level
	*/
	private function parsNextLevel($value, $nextLevel) {
		foreach($value->attributes() as $key0 => $value1){
			if($nextLevel=="linkFound") {
				switch ($key0) { 
					case "rel":
						//not used
						break;
					case "type":
						$this->type = $value1;
						break;
					case "href":
						switch ($this->type) { 
							case "application/atom+xml":
								$this->rssLink = $value1;
								break;
							case "text/html":
								$this->albumLink = $value1;
								break;
							case "application/x-shockwave-flash":
								$this->flashLink = $value1;
								break;
						}
						break;
				}
			}
		}
	}
	
	/**
	* pars last level
	*/
	private function parsLastLevel($value, $nextLevel) {
		foreach($value as $key => $value2) {  
			if($nextLevel=="authorFound") {
				switch ($key) { 
					case "name":
						$this->author = $value2;
						break;
					case "uri":
						$this->uri = $value2;
						break;
				}
			}
			if($nextLevel=="entryFound") {
				switch ($key) { 
					case "title":
						$this->albumTitlesArrayObj->append($value2);
						$tempTitle = $value2;
						break;
					case "link":
						$linkAlbum=TRUE;
						break;
				}
			}
			foreach($value2->attributes() as $attributeskey => $attributesvalue2){
				if($linkAlbum) {
					switch ($attributeskey) { 
						case "type":
							$this->type = $attributesvalue2;
							break;
						case "href":
							switch ($this->type) { 
								case "application/atom+xml":
									//$this->rssLink = $value1;
									break;
								case "text/html":
									$this->albumLinksArrayObj->append($attributesvalue2);
									$this->albumTitlesAndLinksArray->offsetSet((string)$tempTitle,$attributesvalue2);
									break;
								case "application/x-shockwave-flash":
									//$this->flashLink = $value1;
									break;
							}
							break;
						
					}
				} 
			}
		}
	
	}
	
	/**
	* return title 
	*/
	function getTitle(){ 
		return $this->title;
    }  

	/**
	* return last updated time
	*/
	function getUpDated(){ 
		return $this->updated;
    }  
	
	/**
	* return last user ICON
	*/
	function getIcon(){ 
		return $this->icon;
    }  

	/**
	* return link to flash page
	*/
	function getFlashLink(){ 
		return $this->flashLink;
    } 	
	
	/**
	* return link to rss feed
	*/
	function getRSSLink(){ 
		return $this->rssLink;
    } 
	
	/**
	* return link to rss feed
	*/
	function getAlbumLink(){ 
		return $this->albumLink;
    }
	
	/**
	* return Author
	*/
	function getAuthor(){ 
		return $this->author;
    }
	
	/**
	* return URI
	*/
	function getUri(){ 
		return $this->uri;
    }
	
	/**
	* return array of album titles
	*/
	function getAlbumTitles(){ 
		return $this->albumTitlesArrayObj;
    }
	
	/**
	* return array of album links (URL)
	*/
	function getAlbumLinks(){ 
		return $this->albumLinksArrayObj;
    }
	
	/**
	* return array of album links (URL) and matching album names.
	*/
    function getAlbumTitleAndLinks(){ 
		return $this->albumTitlesAndLinksArray;
    }
	
}

?>



