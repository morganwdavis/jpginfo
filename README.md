jpginfo
=======

PHP function to efficiently read JPG file for size and optimization info.

This is an alternative to PHP's getimagesize() function (http://php.net/manual/en/function.getimagesize.php) which reportedly reads the entire JPG file before returning info. I needed something much faster and the ability to report the JPG's optimization type, "baseline" or "progressive", which getimagesize() does not return.

On the getimagesize() PHP manual page, the top-voted script that attempts a similar goal was not as elegant and efficient, and possibly not as accurate in parsing all types of JPG files.  So here is my contribution.

It wouldn't take much work to return the application-specific (APPn) records by adding conditions to the switch statement, but as I didn't need them, that code is omitted.

Even though it seems to be much faster than PHP's built-in function, especially when processing large images, I would love for it to be even faster.
