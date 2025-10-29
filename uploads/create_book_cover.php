<?php
// Create HTML & CSS Programming Book Cover Image
$width = 400;
$height = 500;

// Create image
$image = imagecreate($width, $height);

// Define colors
$black = imagecolorallocate($image, 40, 40, 40);
$yellow = imagecolorallocate($image, 255, 193, 7);
$white = imagecolorallocate($image, 255, 255, 255);
$gray = imagecolorallocate($image, 128, 128, 128);

// Fill background with black
imagefill($image, 0, 0, $black);

// Add title "HTML" in yellow
$font_size = 5;
imagestring($image, $font_size, 50, 50, '<HTML/>', $yellow);

// Add subtitle "& CSS" in white
imagestring($image, $font_size, 50, 80, '& {CSS}', $white);

// Add subtitle
imagestring($image, 3, 50, 120, 'EASY FOR NON-CODERS', $white);

// Add author
imagestring($image, 2, 50, 160, 'CODING GUIDE', $gray);

// Add bottom text
imagestring($image, 2, 50, 400, 'RICH PROJECTS', $yellow);
imagestring($image, 1, 50, 420, 'Including responsive, forms, design, navigation', $white);
imagestring($image, 1, 50, 440, 'and much more all explained step by step', $white);

// Save the image
$filename = 'html-css-programming-book.jpg';
imagejpeg($image, $filename, 90);
imagedestroy($image);

echo "Book cover image created: $filename\n";
?>