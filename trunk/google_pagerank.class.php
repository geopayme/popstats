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
 * Google PageRank Fetcher
 *
 * Requires an API key to access Technorati stats. 
 * (Get one at http://technorati.com/developers/apikey.html)
 *
 * @author Mark Woodman, http://techbrew.net 
 *         Based on original code presumed to be in the public domain.
 *
 * $URL$
 * $Rev$
 * $Author$
 * $Date$
 * $Id$
 */
class GooglePageRank 
{
  var $site;
  var $pagerank;
  
   /**
    * Constructor.
    *
    * @param url        The site URL to check for PageRank
    * @param cacheTime  (Optional) Length of time in seconds to cache results.
    */
   function GooglePageRank($site, $cacheTime=86400) 
   {
      $this->site = $site;
      if(count($site)==0) die('Google needs a site URL to check pagerank.');
      
      // Calculated variables
      $info = 'info:' . urldecode($site);
      $checksum = $this->checksum($this->strord($info));
      $url = "http://www.google.com/search?client=navclient-auto&ch=6{$checksum}&features=Rank&q={$info}";
      
      // Pull pagerank through cache
      $cacher = new Cacher('_google');
      $result = $cacher->fetchContents($url, $cacheTime);
      
      // Parse results
      $this->raw = trim($result);
      preg_match('/Rank_[0-9]:[0-9]:(.*)/', $result, $r);
      if(!isset($r[1]))
      {
         trigger_error("Couldn't get Pagerank for {$site}.  Got: [{$result}]", E_USER_NOTICE);
         error_log("\n" . date('r') . "Couldn't get Pagerank for {$site}", 3, 'error_log');
         $this->pagerank = 0;
      }
      else
      {
         $this->pagerank =(isset($r[1])) ? $r[1] : 'Error';
      }
   }
   
    /**
     * Converts number to int 32
     * (Required for pagerank hash)
     */
    function to_int_32 (&$x) {
      $z = hexdec(80000000);
      $y = (int) $x;
      if($y ==- $z && $x <- $z){
       $y = (int) ((-1) * $x);
       $y = (-1) * $y;
      }
      $x = $y;
    }
    
    /**
     * Fills in zeros on a number
     * (Required for pagerank hash)
     */
    function zero_fill ($a, $b) {
      $z = hexdec(80000000);
      if ($z & $a) {
        $a = ($a >> 1);
        $a &= (~$z);
        $a |= 0x40000000;
        $a = ($a >> ($b - 1));
      } else {
        $a = ($a >> $b);
      }
      return $a;
    }
    
    /**
     * Pagerank hash prerequisites
     */
    function mix($a, $b, $c) {
      $a -= $b; $a -= $c; $this->to_int_32($a); $a = (int)($a ^ ($this->zero_fill($c,13)));
      $b -= $c; $b -= $a; $this->to_int_32($b); $b = (int)($b ^ ($a<<8));
      $c -= $a; $c -= $b; $this->to_int_32($c); $c = (int)($c ^ ($this->zero_fill($b,13)));
      $a -= $b; $a -= $c; $this->to_int_32($a); $a = (int)($a ^ ($this->zero_fill($c,12)));
      $b -= $c; $b -= $a; $this->to_int_32($b); $b = (int)($b ^ ($a<<16));
      $c -= $a; $c -= $b; $this->to_int_32($c); $c = (int)($c ^ ($this->zero_fill($b,5)));
      $a -= $b; $a -= $c; $this->to_int_32($a); $a = (int)($a ^ ($this->zero_fill($c,3)));
      $b -= $c; $b -= $a; $this->to_int_32($b); $b = (int)($b ^ ($a<<10));
      $c -= $a; $c -= $b; $this->to_int_32($c); $c = (int)($c ^ ($this->zero_fill($b,15)));
      return array($a,$b,$c);
    }
    
    /**
     * Pagerank checksum hash emulator
     */
    function checksum ($url, $length = null, $init = 0xE6359A60) {
      if (is_null($length)) {
        $length = sizeof($url);
      }
      $a = $b = 0x9E3779B9;
      $c = $init;
      $k = 0;
      $len = $length;
      while($len >= 12) {
      $a += ($url[$k+0] + ($url[$k+1] << 8) + ($url[$k+2] << 16) + ($url[$k+3] << 24));
      $b += ($url[$k+4] + ($url[$k+5] << 8) + ($url[$k+6] << 16) + ($url[$k+7] << 24));
      $c += ($url[$k+8] + ($url[$k+9] << 8) + ($url[$k+10] << 16) + ($url[$k+11] << 24));
      $mix = $this->mix($a,$b,$c);
      $a = $mix[0]; $b = $mix[1]; $c = $mix[2];
      $k += 12;
      $len -= 12;
      }
      $c += $length;
      switch($len) {
        case 11: $c += ($url[$k + 10] << 24);
        case 10: $c += ($url[$k + 9] << 16);
        case 9: $c += ($url[$k + 8] << 8);
        case 8: $b += ($url[$k + 7] << 24);
        case 7: $b += ($url[$k + 6] << 16);
        case 6: $b += ($url[$k + 5] << 8);
        case 5: $b += ($url[$k + 4]);
        case 4: $a += ($url[$k + 3] << 24);
        case 3: $a += ($url[$k + 2] << 16);
        case 2: $a += ($url[$k + 1] << 8);
        case 1: $a += ($url[$k + 0]);
      }
      $mix = $this->mix($a, $b, $c);
      return $mix[2];
    }
    
    /**
     * ASCII conversion of a string
     */
    function strord($string) {
      for($i = 0; $i < strlen($string); $i++) {
        $result[$i] = ord($string{$i});
      }
      return $result;
    }
    
    /**
     * Number formatting for use with pagerank hash
     */
    function format_number ($number='', $divchar = ',', $divat = 3) {
      $decimals = '';
      $formatted = '';
      if (strstr($number, '.')) {
        $pieces = explode('.', $number);
        $number = $pieces[0];
        $decimals = '.' . $pieces[1];
      } else {
        $number = (string) $number;
      }
      if (strlen($number) <= $divat)
        return $number;
        $j = 0;
      for ($i = strlen($number) - 1; $i >= 0; $i--) {
        if ($j == $divat) {
          $formatted = $divchar . $formatted;
          $j = 0;
        }
        $formatted = $number[$i] . $formatted;
        $j++;
      }
      return $formatted . $decimals;
    }

  }
?>