jpginfo
=======

PHP function to efficiently read JPG file for size and optimization info.

This is an alternative to PHP's `getimagesize()` function (http://php.net/manual/en/function.getimagesize.php) which reportedly reads the entire JPG file before returning info. I needed something much faster and the ability to report the JPG's optimization type, "baseline" or "progressive", which getimagesize() does not return.

On the `getimagesize()` PHP manual page, the top-voted script that attempts a similar goal was not as elegant and efficient, and possibly not as accurate in parsing all types of JPG files.  So here is my contribution.

It wouldn't take much work to return the application-specific (APPn) records by adding conditions to the switch statement, but as I didn't need them, that code is omitted.

Even though it seems to be much faster than PHP's built-in function, especially when processing large images, I would love for it to be even faster, hence sharing here.

Usage
=====

```
mixed jpginfo ( string $filename )
```

The `jpginfo()` function will determine the size of any given image file and return the dimensions along with the optimization type.

Parameters
----------
* `filename`: This parameter specifies the file you wish to retrieve information about. It can reference a local file or (configuration permitting) a remote file using one of the supported streams.

Return Values
-------------
Returns `false` on error (e.g., unable to read file data, invalid JPG data markers, etc.), or an associative array with the following elements:

* `height`: Height in pixels
* `width`: Width in pixels
* `progressive`: `true` for progressive optimization, `false` for baseline optimization
* `bits`: The bit depth of the image (usually 8)


Notes
-----
Not all streams support seeking. For those that do not support seeking, forward seeking from the current position is accomplished by reading and discarding data; other forms of seeking will fail.  Therefore, jpginfo works most optimally on local filesystem files.


