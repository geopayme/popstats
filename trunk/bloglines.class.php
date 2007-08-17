<?php
/**
 * TechBrew.net's "popstats" is available from http://code.google.com/p/popstats/
 *  
 * This work is dual-licensed under the GNU Lesser General Public License
 * and the Creative Commons Attribution-Share Alike 3.0 License. 
 * Copies or derivatives must retain both attribution and licensing statement.
 *
 * To view a copy of these licenses, visit:
 * http://creativecommons.org/licenses/by-sa/3.0/
 * http://www.gnu.org/licenses/lgpl.html
 *
 * This software is provided AS-IS with no warranty whatsoever.
 */
 
require_once('cacher.class.php');

/**
 * Bloglines Stats Fetcher
 *
 * Requires an API key to access Bloglines stats. 
 * http://www.bloglines.com/services/api/search
 *
 * @author Mark Woodman, http://techbrew.net 
 * @version 15 April 2007
 *
 */
class Bloglines
{
   var $cacher;
   var $searchBase;
   var $stats = array();
   var $raw;

   /**
    * Constructor for the Bloglines Stats Fetcher
    *
    * @param apikey     API key provided by Bloglines
    * @param site       URI of the site to get stats on
    * @param cacheTime  (Optional) Length of time in seconds to cache results.
    * @return           Boolean.  True if stats retrieved, false otherwise.
    */
   function Bloglines($user, $apikey, $site, $cacheTime=86400)
   {
      // Use Cacher to get requested documents
      $this->cacher = new Cacher('_bloglines');
      
      // Compose base of search URI to Bloglines API
      $this->searchBase = "http://www.bloglines.com/search?format=publicapi&apiuser={$user}&apikey={$apikey}&t=f&q=";
      
      // Add stats from site
      return $this->addUrl($site,$cacheTime);
   }
   
   /**
    * Allows additional URLs for a site to be added to the search results.
    *
    * @param site       URI of the site to get stats on
    * @param cacheTime  (Optional) Length of time in seconds to cache results.
    */
   function addUrl($site, $cacheTime=86400)
   {
      // Compose URI to Bloglines API
      $uri = $this->searchBase . urlencode($site);

      // Open uri or fail
      try
      {
         $raw = $this->cacher->fetchContents($uri, $cacheTime);
      }
      catch(Exception $e)
      {
         $this->cacher->clear($uri);
         $this->error = 'Could not contact bloglines';
      }
      if(!$raw) return false;
      $this->raw = $raw;
      
      // Read output into an XML DOM
      try
      {
         $doc = new SimpleXMLElement($raw);
      }
      catch(Exception $e)
      {
         $this->cacher->clear($uri);
         $this->error = $e;
      }
      
      // Locate all site elements in DOM
      if(!$doc) 
      {
        trigger_error("Unable to parse Bloglines info for ${site}", E_USER_NOTICE);
        return false;
      }
      $siteElements = $doc->xpath('/publicapi/resultset[@set="main"]/result/site');
      if(!$siteElements) return false;
      
      // Iterate through sites in search results
      foreach ($siteElements as $siteElement) 
      {
         $site = array();
         $site['subscribers'] = (string) $siteElement['nsubs'];
         $site['name'] = (string) $siteElement->name;
         $site['url'] = (string) $siteElement->url;
         $site['feedurl'] = (string) $siteElement->feedurl;
         
         // Index by feed URL to prevent duplicates
         $this->stats[$site['feedurl']] = $site;
      }
   }
   
   /**
    * Convenience function to return the maximum subscribers
    * to any one feed found by the search.
    */
   function maxSubscribers()
   {
      if(isset($this->error)) return 'Error';
      
      $max = 0;
      foreach($this->stats as $site)
      {
         if($site['subscribers']>$max)
         {
            $max = $site['subscribers'];
         }
      }  
      return $max;
   }
   
   /**
    * Convenience function to return the total subscribers
    * to all feeds found by the search.
    */
   function totalSubscribers()
   {
      $total = 0;
      foreach($this->stats as $site)
      {
         $total = $total + $site['subscribers'];
      }  
      return $max;
   }
}

?>