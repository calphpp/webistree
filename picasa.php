<?php

/**
 * @file
 * @author  Mark Elo 
 * @version 1.0
 *
 * @section LICENSE 
 * Simple Picasa Albums Class
 * 
 * This returns Picasa Web Albums data. As we are not modifying any data OAuth is not needed, only the desired Google Username is required.
 * 
 * webistree.com
 * By Mark Elo (http://www.calphpp.com)
 * July 24, 2011
 *
 * Example:
 *
 * $user = your gmail login: your.name@google.com
 * $picasaAlbum = new simplePicasa();
 * $albumHeader = $picasaAlbum->getAlbumHeader($user); 
 * print "<img src='".$albumHeader['icon']."' ><br />";
 * print $albumHeader['title']."<br />";
 * print "<a href='".$albumHeader['Feed']."'>Feed</a> <br />";
 * foreach($picasaAlbum->getEntryID($user) as $value){
 *		$photoHeader = $picasaAlbum->getAlbumEntry($value);
 *		print "<h1>".$photoHeader['title']."</h1>";
 *		print "<img src='".$photoHeader['icon']."'><br />";
 * 		print "<a href='".$albumHeader['SlideShow']."'>Slideshow</a> <br />";
 *		$photoDetail = $picasaAlbum->getPhotoEntry($photoHeader['Feed']);
 *		foreach ($photoDetail['url'] as $key => $value) {
 *			print "Title: ".$photoDetail['title'][$key]."<br />"; 
 *			print "Updated: ".$photoDetail['updated'][$key]."<br />"; 
 *			print "Author: ".$photoDetail['authorName'][$key]."<br />"; 
 *			print "Published: ".$photoDetail['published'][$key]."<br />";  
 *			print "Summary: ".$photoDetail['summary'][$key]."<br />"; 				
 *			print "<a href='".$value."'><img src='".$photoDetail['thumbnail0'][$key]."'><img src='".$photoDetail['thumbnail1'][$key]."'><img src='".$photoDetail['thumbnail2'][$key]."'></a><br \>"; 
 *		}
 * };
 * 
 * Methods and Usage:
 *
 * getAlbumHeader($user) 				// Returns Picasa overall header details in an array.
 * 		$albumHeader['id'] 				// Album ID
 *		$albumHeader['icon'] 			// User Icon
 *		$albumHeader['updated'] 		// Updates
 *		$albumHeader['published'] 		// Published	
 *		$albumHeader['title'] 			// Title
 *		$albumHeader['subTitle']    	// Sub Title
 *		$albumHeader['categoryScheme']  // Scheme
 *		$albumHeader['categoryTerm']  	// Term	
 *		$albumHeader['authorName'] 		// Author Name
 *		$albumHeader['authorUri'] 		// Author URI	
 *		$albumHeader['Link']			// Link to album	
 *		$albumHeader['Feed'] 			// Link to feed
 *		$albumHeader['ID'] 				// Link to ID
 * getEntryID($user)					// returns an Array of all photo labum ID's
 * getAlbumEntry(ID)					// returns an array of each phptp album
 * 		$photoHeader['id']				// Album ID
 *		$photoHeader['icon']			// User Icon
 *		$photoHeader['updated'] 		// Updates	
 *		$photoHeader['published']    	// Published
 *		$photoHeader['title'] 			// Title
 *		$photoHeader['subTitle'] 		// Sub Title
 *		$photoHeader['categoryScheme']  // Scheme
 *		$photoHeader['categoryTerm'] 	// Term	
 *		$photoHeader['authorName']  	// Author Name	
 *		$photoHeader['authorUri'] 		// Author URI	
 *		$photoHeader['SlideShow'] 		// Link to Slide Show	
 *		$photoHeader['Link'] 			// Link to album	
 *		$photoHeader['Feed'] 			// Link to feed
 *		$photoHeader['ID'] 				// Link to ID
 * getPhotoEntry($photoHeader['Feed'])	// returns a multidimension array of each photo and its associated elements.
 *		$photoNumber 0 to n, where n is the number of photos in each album
 * 		$photoDetail['title'][$photoNumber] 		// Photo Title
 *		$photoDetail['updated'][$photoNumber] 		// Updates		
 *		$photoDetail['published'][$photoNumber] 	// Published
 *		$photoDetail['authorName'][$photoNumber] 	// Author Name
 *		$photoDetail['authorUri'][$photoNumber] 	// Author URI
 *		$photoDetail['summary'][$photoNumber] 		// Summary
 *		$photoDetail['thumbnail0'][$photoNumber] 	// Small Thumb
 *		$photoDetail['thumbnail1'][$photoNumber] 	// Midle Thumb
 *		$photoDetail['thumbnail2'][$photoNumber] 	// Big Thumb
 *		$photoDetail['url'][$photoNumber] 			// Link to big picture
 */

/**
 * \brief simple Picasa
 */ 
	
class simplePicasa {  

	public $albumHeader = array();
	public $photoHeader = array();
	public $photoDetail = array();
	
	function __construct($user){ 

	}
	
	/**
	* sets the user for the albums to retrieve and returns an arroay of teh Album Id's
	*/
	
	function setUser($user) {
		$feed = 'http://picasaweb.google.com/data/feed/api/user/' . $user . '?v=2';
		$xml = simplexml_load_file($feed);
		print $this->getEntryID($xml);
		$this->getAlbumHeader($xml);
		return $this->getEntryID($xml);
	}
	
	function getAlbumEntry($photoId) {
		$photosXML = simplexml_load_file($photoId);
		$photoHeader = $this->getPhotoHeader($photosXML);
		return $photoHeader; 
	}
	
	/** 
	* get each photo entry, creates a two array of each element with index
	*/
	function getPhotoEntry($photoId) {
		$photosXML = simplexml_load_file($photoId);
		$photoHeader = $this->getPhotoHeader($photosXML);
		foreach($photosXML->entry as $photo){
			$media = $photo->children('http://search.yahoo.com/mrss/');
			$group_content = $media->group->content; 
			$photoDetail['title'][] = $photosXML->title;
			$photoDetail['updated'][] = $photosXML->updated;
			$photoDetail['published'][] = $photosXML->published;
			$photoDetail['authorName'][] = $photosXML->author->name;
			$photoDetail['authorUri'][] = $photosXML->author->uri;
			$photoDetail['summary'][] = $photosXML->summary;
			$photoDetail['thumbnail0'][] = $media->group->thumbnail[0]->attributes()->{'url'}; 
			$photoDetail['thumbnail1'][] = $media->group->thumbnail[1]->attributes()->{'url'};
			$photoDetail['thumbnail2'][] = $media->group->thumbnail[2]->attributes()->{'url'}; 
			$photoDetail['url'][] = $group_content->attributes()->{'url'};
		}
		return $photoDetail;
	}
	
	/** 
	* get Album header, creates an array of the album header, ID, Title, Sub Title, Icon, updated, and Author
	*/
	function getAlbumHeader($user) { 
		$xml = $this->getSimpleXML($user);
		$albumHeader['id'] = $xml->id;	
		$albumHeader['icon'] = $xml->icon;
		$albumHeader['updated'] = $xml->updated;
		$albumHeader['published'] = $xml->published;
		$albumHeader['updated'] = $this->updated;	
		$albumHeader['title'] = $xml->title;
		$albumHeader['subTitle'] = $xml->subTitle;
		$albumHeader['categoryScheme'] = $xml->category->attributes()->scheme;
		$albumHeader['categoryTerm'] = $xml->category->attributes()->term;		
		$albumHeader['authorName'] = $xml->author->name;
		$albumHeader['authorUri'] = $xml->author->uri;
		$links = $this->getLinks($xml);
		$albumHeader['SlideShow'] = $links['SlideShow'];
		$albumHeader['Link'] = $links['Link'];		
		$albumHeader['Feed'] = $links['Feed'];
		$albumHeader['ID'] = $links['ID'];
		return $albumHeader;
	}
	
	/** 
	* get Album header, creates an array of the photo album header, ID, Title, Sub Title, Icon, updated, and Author
	*/
	function getPhotoHeader($xml) {  
		$photoHeader['id'] = $xml->id;	
		$photoHeader['icon'] = $xml->icon;
		$photoHeader['updated'] = $xml->updated;
		$photoHeader['published'] = $xml->published;
		$photoHeader['updated'] = $this->updated;	
		$photoHeader['title'] = $xml->title;
		$photoHeader['subTitle'] = $xml->subTitle;
		$photoHeader['categoryScheme'] = $xml->category->attributes()->scheme;
		$photoHeader['categoryTerm'] = $xml->category->attributes()->term;		
		$photoHeader['authorName'] = $xml->author->name;
		$photoHeader['authorUri'] = $xml->author->uri;
		$links = $this->getLinks($xml);
		$photoHeader['SlideShow'] = $links['SlideShow'];
		$photoHeader['Link'] = $links['Link'];		
		$photoHeader['Feed'] = $links['Feed'];
		$photoHeader['ID'] = $links['ID'];
		return $photoHeader;
	}
	
	/** 
	* get Album links, returns an array of rel, type and href attributes
	*/
	private function getLinks($xml) {
		$linkElements = array();
		foreach($xml->link as $links) { 
		    switch ($links->attributes()->rel) { 
				case "http://schemas.google.com/photos/2007#slideshow":
					$linkElements['SlideShow'] = $links->attributes()->href;
					break;
				case "alternate":
					$linkElements['Link'] = $links->attributes()->href;
					break; 
				case "http://schemas.google.com/g/2005#feed":
					$linkElements['Feed'] = $links->attributes()->href;
					break;
				case "self":
					$linkElements['ID'] = $links->attributes()->href;
					break;
				case "edit":
					$linkElements['edit'] = $links->attributes()->href;
					break;
			}
		}
		return $linkElements;
	}
	/** 
	* get Album entries [entry], returns an array
	*/
	function getEntryID($user) {
	    $simpleXML = $this->getSimpleXML($user);
		foreach($simpleXML->entry as $entries) { 
			$links = $this->getLinks($entries);		
			$photoAlbumEntryID[$cnt++] = $links['Feed'];
		}
		return $photoAlbumEntryID;
	}
	
	/** 
	* get call data feed with user name and return simple XML array.
	*/
	private function getSimpleXML($user) {
		$feed = 'http://picasaweb.google.com/data/feed/api/user/' . $user . '?v=2';
		return simplexml_load_file($feed);
	}

}

?>
