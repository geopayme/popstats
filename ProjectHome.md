# Popstats Overview #
A loosely-associated set of tools written in PHP 5 that facilitate API-level access to various data providers on the web.  These data providers provide metrics as to the popularity of a website based on their own internal criteria.

The intended audience for these tools are PHP programmers and integrators; these are not intended for use by an end-user or non-programmer.

You are highly encouraged to contribute to this project by submitting improvements or bugfixes that have general benefit to the tools.

# Classes #
| **Class** | **Purpose** |
|:----------|:------------|
| [cacher.class.php](http://popstats.googlecode.com/svn/trunk/cacher.class.php) | Caching utility class which allows you to cache the results of a GET.  This helps you avoid spam-requesting services like the Technorati API. |
|           |             |
| [bloglines.class.php](http://popstats.googlecode.com/svn/trunk/bloglines.class.php) | Gets the Bloglines subscribers for one or more URLs.  (_Requires [cacher.class.php](http://popstats.googlecode.com/svn/trunk/cacher.class.php)_) |
| [google\_pagerank.class.php](http://popstats.googlecode.com/svn/trunk/google_pagerank.class.php) | Gets the Google PageRank for a URL. (_Requires [cacher.class.php](http://popstats.googlecode.com/svn/trunk/cacher.class.php)_) |
| [technorati.class.php](http://popstats.googlecode.com/svn/trunk/technorati.class.php) | Gets the Technorati rank for a URL. (_Requires [cacher.class.php](http://popstats.googlecode.com/svn/trunk/cacher.class.php)_) |


# Licencing #

This work is dual-licensed under the GNU Lesser General Public License
and the Creative Commons Attribution-Share Alike 3.0 License.
Copies or derivatives must retain both attribution and licensing statement.

To view a copy of these licenses, visit:
http://creativecommons.org/licenses/by-sa/3.0/
and
http://www.gnu.org/licenses/lgpl.html

