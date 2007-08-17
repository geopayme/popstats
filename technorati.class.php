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
 * Technorati Stats Fetcher
 *
 * Requires an API key to access Technorati stats. 
 * (Get one at http://technorati.com/developers/apikey.html)
 *
 * @author Mark Woodman, http://techbrew.net 
 * @version 15 April 2007
 *
 * TODO: Capture more than just site rank
 */
class Technorati
{
   var $rank;
   var $site;
 
   /**
    * Constructor for the Technorati Stats Fetcher
    *
    * @param apikey     API key provided by Technorati
    * @param site       URI of the site to get stats on
    * @param cacheTime  (Optional) Length of time in seconds to cache results
    *                   Warning:  The API limits you to a fixed number
    *                   of calls per 24 hours.  Using a cache time of less
    *                   than a day (86400 seconds) is probably unneccessary
    *                   and may get you blocked for the day.
    *
    * @return           Boolean.  True if stats retrieved, false otherwise.
    */
   function Technorati($apikey, $site, $cacheTime=86400)
   {
      // Keep site URI on the instance
      $this->site = $site;
      
      // Compose URI to Technorati API
      $encodedSite = urlencode($site);
      $uri = "http://api.technorati.com/bloginfo?key={$apikey}&url={$encodedSite}";
      
      // Open uri or fail
      $cacher = new Cacher('_technorati');
      $raw = $cacher->fetchContents($uri, 86400);
      $this->raw = $raw;
      
      // Read output into an XML DOM
      try
      {
         $doc = new SimpleXMLElement($raw);
      }
      catch(Exception $e)
      {
         error_log("\n" . date('r') . " - Bad XML in Technorati output:\n{$raw}", 3, 'error_log');
         $this->rank=0;
         return false;
      }
      
      // Locate all rank elements in DOM
      $rankElement = $doc->xpath('/tapi/document/result/weblog/rank');
      if(!isset($rankElement)) 
      {
         error_log("\n" . date('r') . " - Cant find rank in Technorati output:\n{$raw}", 3, 'error_log');
         $this->rank=0;
         return false;
      }
      else
      {
         $this->rank = (string) $rankElement[0];
      }
      
      // Success
      return true;
   }
   
   /**
    * Gets the technorati rank for the site.
    */
   function getRank()
   {
      return $this->rank;  
   }
   
   function getSite()
   {
      return $this->site;
   }
}

?>