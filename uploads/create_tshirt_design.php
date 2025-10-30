<?php
// Create Philippine Traditional T-Shirt Design
$width = 400;
$height = 500;

// Create image
$image = imagecreate($width, $height);

// Define colors
$black = imagecolorallocate($image, 30, 30, 30);
$white = imagecolorallocate($image, 255, 255, 255);
$gray = imagecolorallocate($image, 180, 180, 180);
$darkgray = imagecolorallocate($image, 80, 80, 80);
$blue = imagecolorallocate($image, 0, 123, 255);
$red = imagecolorallocate($image, 255, 0, 0);
$yellow = imagecolorallocate($image, 255, 255, 0);

// Fill background with dark color
imagefill($image, 0, 0, $black);

// Draw t-shirt outline
$points = array(
    50, 100,   // left shoulder
    100, 80,   // left neck
    150, 80,   // center neck
    200, 80,   // right neck  
    250, 100,  // right shoulder
    250, 150,  // right armpit
    200, 150,  // right body start
    200, 450,  // right bottom
    100, 450,  // left bottom
    100, 150,  // left body start
    50, 150    // left armpit
);
imagefilledpolygon($image, $points, 6, $darkgray);

// Add Philippine sun design in center
$centerX = 150;
$centerY = 250;

// Draw sun rays (simplified)
for ($i = 0; $i < 8; $i++) {
    $angle = ($i * 45) * pi() / 180;
    $x1 = $centerX + cos($angle) * 30;
    $y1 = $centerY + sin($angle) * 30;
    $x2 = $centerX + cos($angle) * 50;
    $y2 = $centerY + sin($angle) * 50;
    imageline($image, $x1, $y1, $x2, $y2, $yellow);
    imageline($image, $x1+1, $y1, $x2+1, $y2, $yellow);
    imageline($image, $x1, $y1+1, $x2, $y2+1, $yellow);
}

// Draw center circle
imagefilledellipse($image, $centerX, $centerY, 40, 40, $white);
imageellipse($image, $centerX, $centerY, 40, 40, $black);

// Add decorative patterns
// Left side pattern
for ($y = 120; $y < 400; $y += 20) {
    imageline($image, 110, $y, 130, $y, $gray);
    imageline($image, 110, $y+5, 125, $y+5, $gray);
}

// Right side pattern  
for ($y = 120; $y < 400; $y += 20) {
    imageline($image, 170, $y, 190, $y, $gray);
    imageline($image, 175, $y+5, 190, $y+5, $gray);
}

// Add text "PILIPINAS"
imagestring($image, 2, 110, 350, 'TRADITIONAL', $white);
imagestring($image, 2, 115, 370, 'DESIGN', $white);

// Add Philippine flag colors (small accents)
imagefilledrectangle($image, 280, 120, 290, 130, $blue);
imagefilledrectangle($image, 280, 135, 290, 145, $red);
imagefilledrectangle($image, 280, 150, 290, 160, $yellow);

// Save the image
$filename = 'philippine-traditional-tshirt.jpg';
imagejpeg($image, $filename, 90);
imagedestroy($image);

echo "Philippine traditional t-shirt image created: $filename\n";
?>