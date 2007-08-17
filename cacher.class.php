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

/**
 * Cacher utility to write the output of a remote URI request
 * to the local filesystem.
 *
 * @author Mark Woodman, http://techbrew.net 
 * @version 15 April 2007
 */
class Cacher
{
   var $cachedir = CACHE_DIR;
   var $suffix;

   /**
    * Constructor.  Requires CACHE_DIR directive to be set
    * prior to use.  CACHE_DIR will contain cache files
    * which hold the contents of requested URLs.
    */
   function Cacher($suffix='')
   {
      if(!(CACHE_DIR))
      {
         die('CACHE_DIR not configured.');
      }
      $this->suffix = $suffix;
   }
   
   /**
    * Fetch a URL.  If it has already
    * been cached within the specified cacheTime,
    * the cached copy is returned.  Otherwise a
    * fresh copy is retrieved and cached.
    *
    * If it can't write to the cache for some reason, the original URL
    * is returned.
    *
    * @param   $url        The URL to retrieve
    * @param   $cacheTime  The length of time to cache the requested URL.
    */
   function fetch($url, $cacheTime=86600)
   {  	 
      // Determine cache file name
      $cacheFile = $this->cachedir . md5($url) . $this->suffix . '.cache' ;
      $refresh = true;
      if(@file_exists($cacheFile))
      {
         $refresh = (time() - $cacheTime > @filemtime($cacheFile)) ;
      }
      @clearstatcache();
      
     // Cache file if needed
     if($refresh) 
     {
         try
         {
            $tries = 0;
            $errors = 0;
            $contents = false;
            while($tries<3)
            {
               $tries++;
               if(!$contents)
               {
                  $contents = @file_get_contents($url);
               }
               if(!$contents)
               {
                  $error = $error .(' GET_FAIL ');
               }
               else
               {
                  $result = @file_put_contents($cacheFile, $contents);
                  if(!$result)
                  {
                     $error = $error .(' PUT_FAIL ');
                  }
                  else
                  {
                     return $cacheFile;   
                  }
               }
               // TODO:  Is this necessary?  Is there a better way?
               usleep(10000);
            }
            error_log("\n" . date('r') . " - Failed to cache: {$url}", 3, 'error_log');
            error_log("\n" . date('r') . " - Failure reasons: {$error}", 3, 'error_log');
            return false;
         }
         catch(Exception $e)
         {
            error_log("\n" . date('r') . " - {$e}", 3, 'error_log');
            error_log("\n" . date('r') . " - Cacher error: {$error}", 3, 'error_log');
            return false;
         }
         
     }
     return $cacheFile;
   } 
   
   /**
    * Fetch a URL and return contents as a string.  If it has already
    * been cached within the specified cacheTime,
    * the cached copy is returned.  Otherwise a
    * fresh copy is retrieved and cached.
    *
    * @param   $url        The URL to retrieve
    * @param   $cacheTime  The length of time to cache the requested URL.
    */
   function fetchContents($url, $cacheTime=86600)
   {
      $file = $this->fetch($url, $cacheTime);
      if(!$file) return false;
      return file_get_contents($file);   
   }
   
   /**
    * Clear cache files for a url.
    */
   function clear($url)
   {
      // Determine cache file name
      $cacheFile = $this->cachedir . md5($url) . $this->suffix . '.cache' ;
      if(@file_exists($cacheFile))
      {
         @unlink($cacheFile);
      }
   }
}

?>